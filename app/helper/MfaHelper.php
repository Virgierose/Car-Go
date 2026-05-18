<?php
/**
 * MfaHelper.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Handles:
 *   - Email OTP : generate → store in DB → send via Gmail SMTP → verify
 *   - TOTP      : secret generation, QR URI, verification (Google Authenticator)
 *
 * Requirements:
 *   composer require phpmailer/phpmailer
 *
 * Setup:
 *   1. Set SMTP_USERNAME to your Gmail address.
 *   2. Set SMTP_PASSWORD to a Gmail App Password (NOT your Gmail login password).
 *      Generate one at: https://myaccount.google.com/apppasswords
 *      (Requires 2-Step Verification to be enabled on the account.)
 *   3. Make sure vendor/autoload.php is required in your index.php or bootstrap.
 * ─────────────────────────────────────────────────────────────────────────────
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

class MfaHelper
{
    // ── SMTP CREDENTIALS — EDIT THESE TWO LINES ───────────────────────────────
    private const SMTP_USERNAME = 'YOUR_GMAIL@gmail.com';        // ← your Gmail address here
    private const SMTP_PASSWORD = 'xxxx xxxx xxxx xxxx';         // ← 16-char Gmail App Password here
    //   How to get an App Password:
    //   1. Go to https://myaccount.google.com/security
    //   2. Enable 2-Step Verification (required)
    //   3. Go to https://myaccount.google.com/apppasswords
    //   4. Create one named "CarGo" — paste the 16 chars above (spaces are fine)
    // ─────────────────────────────────────────────────────────────────────────

    // ── SMTP CONSTANTS (do not change unless using a different provider) ───────
    private const SMTP_HOST          = 'smtp.gmail.com';
    private const SMTP_PORT          = 587;                 // STARTTLS
    private const MAIL_FROM_NAME     = 'CarGo';

    // ── OTP SETTINGS ──────────────────────────────────────────────────────────
    private const OTP_LENGTH         = 6;
    private const OTP_EXPIRY_MINUTES = 10;

    // ── TOTP SETTINGS ─────────────────────────────────────────────────────────
    private const TOTP_DIGITS        = 6;
    private const TOTP_PERIOD        = 30;   // seconds per window
    private const TOTP_WINDOW        = 1;    // ±1 window to allow clock drift

    // =========================================================================
    // PUBLIC — EMAIL OTP
    // =========================================================================

    /**
     * Generate a 6-digit OTP, persist it to the DB, and email it to the user.
     *
     * @param mysqli $db
     * @param int    $clientId
     * @param string $email      Recipient email address
     * @throws RuntimeException  If mail delivery fails
     */
    public static function sendEmailOtp(mysqli $db, int $clientId, string $email): void
    {
        // 1. Generate cryptographically secure 6-digit code
        $code    = str_pad((string) random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+' . self::OTP_EXPIRY_MINUTES . ' minutes'));

        // 2. Persist to DB *before* attempting send so the expiry is already set
        $stmt = $db->prepare(
            'UPDATE tbl_client
                SET mfa_otp_code = ?, mfa_otp_expires = ?
              WHERE client_id = ?'
        );
        if (!$stmt) {
            throw new RuntimeException('DB prepare failed: ' . $db->error);
        }
        $stmt->bind_param('ssi', $code, $expires, $clientId);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('DB execute failed: ' . $stmt->error);
        }
        $stmt->close();

        // 3. Send the email (throws RuntimeException on any failure)
        self::sendMail($email, $code);
    }

    /**
     * Verify a submitted OTP code against the DB record.
     *
     * Returns TRUE on success and immediately invalidates the code.
     * Returns FALSE for wrong code, expired code, or missing record.
     *
     * @param mysqli $db
     * @param int    $clientId
     * @param string $submitted  Raw input from the user
     */
    public static function verifyEmailOtp(mysqli $db, int $clientId, string $submitted): bool
    {
        // Sanitise: keep digits only
        $submitted = preg_replace('/\D/', '', $submitted);

        if (strlen($submitted) !== self::OTP_LENGTH) {
            return false;
        }

        $stmt = $db->prepare(
            'SELECT mfa_otp_code, mfa_otp_expires
               FROM tbl_client
              WHERE client_id = ?
              LIMIT 1'
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $clientId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || empty($row['mfa_otp_code']) || empty($row['mfa_otp_expires'])) {
            return false;
        }

        // Check expiry
        if (new DateTime('now') > new DateTime($row['mfa_otp_expires'])) {
            return false;
        }

        // Timing-safe comparison — prevents timing attacks
        if (!hash_equals((string) $row['mfa_otp_code'], $submitted)) {
            return false;
        }

        // Invalidate immediately so it cannot be reused
        $clear = $db->prepare(
            'UPDATE tbl_client
                SET mfa_otp_code = NULL, mfa_otp_expires = NULL
              WHERE client_id = ?'
        );
        if ($clear) {
            $clear->bind_param('i', $clientId);
            $clear->execute();
            $clear->close();
        }

        return true;
    }

    // =========================================================================
    // PUBLIC — TOTP (Google Authenticator)
    // =========================================================================

    /**
     * Generate a cryptographically random Base32 TOTP secret.
     */
    public static function generateSecret(int $length = 32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret   = '';
        $bytes    = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[ord($bytes[$i]) & 31];
        }
        return $secret;
    }

    /**
     * Return a QR-code image URL for scanning with Google Authenticator.
     */
    public static function getQrCodeDataUri(string $secret, string $email): string
    {
        $issuer  = urlencode(defined('APP_NAME') ? APP_NAME : 'CarGo');
        $account = urlencode($email);
        $uri     = "otpauth://totp/{$issuer}:{$account}"
                 . "?secret={$secret}&issuer={$issuer}&digits=6&period=30";

        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($uri);
    }

    /**
     * Verify a TOTP code with ±1 window to compensate for clock drift.
     */
    public static function verifyTotp(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (!ctype_digit($code) || strlen($code) !== self::TOTP_DIGITS) {
            return false;
        }

        $counter = (int) floor(time() / self::TOTP_PERIOD);
        for ($i = -self::TOTP_WINDOW; $i <= self::TOTP_WINDOW; $i++) {
            if (hash_equals(self::computeTotp($secret, $counter + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Persist a TOTP secret to the DB.
     */
    public static function saveSecret(mysqli $db, int $clientId, string $secret): void
    {
        $stmt = $db->prepare('UPDATE tbl_client SET mfa_secret = ? WHERE client_id = ?');
        if (!$stmt) {
            throw new RuntimeException('DB prepare failed: ' . $db->error);
        }
        $stmt->bind_param('si', $secret, $clientId);
        $stmt->execute();
        $stmt->close();
    }

    // =========================================================================
    // PRIVATE — MAIL
    // =========================================================================

    /**
     * Send the OTP email via Gmail SMTP (STARTTLS, port 587).
     * Throws RuntimeException with a user-safe message on any failure.
     */
    private static function sendMail(string $to, string $code): void
    {
        $mail = new PHPMailer(true); // true = exceptions enabled

        try {
            // ── Server settings ──────────────────────────────────────────────
            $mail->isSMTP();
            $mail->Host       = self::SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::SMTP_USERNAME;
            $mail->Password   = self::SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = self::SMTP_PORT;
            $mail->CharSet    = PHPMailer::CHARSET_UTF8;
            $mail->Encoding   = PHPMailer::ENCODING_BASE64;

            // Set to SMTP::DEBUG_SERVER temporarily if emails are not sending,
            // then switch back to DEBUG_OFF for production.
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;

            // Increase timeout to avoid false timeouts on slow networks
            $mail->Timeout    = 30;

            // ── Sender / recipient ───────────────────────────────────────────
            $mail->setFrom(self::SMTP_USERNAME, self::MAIL_FROM_NAME);
            $mail->addAddress($to);
            $mail->addReplyTo(self::SMTP_USERNAME, self::MAIL_FROM_NAME);

            // ── Content ──────────────────────────────────────────────────────
            $mail->isHTML(true);
            $mail->Subject = 'Your CarGo Verification Code';
            $mail->Body    = self::buildEmailHtml($code);
            $mail->AltBody = self::buildEmailText($code);

            $mail->send();

        } catch (MailException $e) {
            // Log full error for developers, expose only a safe message upward
            error_log('[CarGo MFA] PHPMailer error: ' . $mail->ErrorInfo);
            throw new RuntimeException(
                'Could not send the verification email. Please try again in a moment.'
            );
        }
    }

    // =========================================================================
    // PRIVATE — EMAIL TEMPLATES
    // =========================================================================

    /**
     * Build the HTML email body.
     */
    private static function buildEmailHtml(string $code): string
    {
        $expiry = self::OTP_EXPIRY_MINUTES;
        $year   = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#f0f0f0;font-family:Arial,Helvetica,sans-serif;">

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
         style="background-color:#f0f0f0;padding:40px 16px;">
    <tr>
      <td align="center">

        <!-- Card -->
        <table role="presentation" width="100%" style="max-width:520px;"
               cellpadding="0" cellspacing="0">

          <!-- ── HEADER ── -->
          <tr>
            <td style="background-color:#c0392b;padding:28px 36px;text-align:center;
                       border-radius:8px 8px 0 0;">
              <div style="font-size:36px;font-weight:900;color:#ffffff;
                          letter-spacing:3px;text-transform:uppercase;
                          font-family:Arial Black,Arial,sans-serif;">
                CAR<span style="color:#f5a5a0;">GO</span>
              </div>
              <div style="margin-top:4px;color:rgba(255,255,255,0.70);
                          font-size:11px;letter-spacing:3px;text-transform:uppercase;">
                Premium Car Rental
              </div>
            </td>
          </tr>

          <!-- ── BODY ── -->
          <tr>
            <td style="background-color:#ffffff;padding:36px 36px 28px;
                       border-left:1px solid #e0e0e0;border-right:1px solid #e0e0e0;">

              <h2 style="margin:0 0 10px;font-size:20px;color:#1a1a1a;font-weight:700;">
                Verify Your Identity
              </h2>
              <p style="margin:0 0 24px;color:#555555;font-size:15px;line-height:1.6;">
                Use the one-time code below to complete your sign-in.
                It will expire in <strong>{$expiry} minutes</strong>.
              </p>

              <!-- OTP Box -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                     style="margin-bottom:24px;">
                <tr>
                  <td align="center"
                      style="background-color:#fafafa;border:2px solid #c0392b;
                             border-radius:6px;padding:22px 10px;">
                    <span style="font-size:42px;font-weight:700;letter-spacing:14px;
                                 color:#c0392b;font-family:'Courier New',Courier,monospace;">
                      {$code}
                    </span>
                  </td>
                </tr>
              </table>

              <p style="margin:0;color:#999999;font-size:13px;line-height:1.6;">
                If you did not request this code, you can safely ignore this email.
                <strong>Never share this code with anyone</strong> — CarGo will never ask for it.
              </p>
            </td>
          </tr>

          <!-- ── FOOTER ── -->
          <tr>
            <td style="background-color:#f7f7f7;padding:16px 36px;
                       border:1px solid #e0e0e0;border-top:none;
                       border-radius:0 0 8px 8px;text-align:center;">
              <p style="margin:0;color:#bbbbbb;font-size:12px;">
                &copy; {$year} CarGo. All rights reserved.
              </p>
            </td>
          </tr>

        </table>
        <!-- /Card -->

      </td>
    </tr>
  </table>

</body>
</html>
HTML;
    }

    /**
     * Build the plain-text fallback email body.
     */
    private static function buildEmailText(string $code): string
    {
        $expiry = self::OTP_EXPIRY_MINUTES;
        return implode("\n", [
            'CarGo — Verify Your Identity',
            str_repeat('─', 40),
            '',
            'Your one-time verification code is:',
            '',
            "    {$code}",
            '',
            "This code expires in {$expiry} minutes.",
            'Never share this code with anyone.',
            '',
            'If you did not request this, please ignore this email.',
            '',
            '© ' . date('Y') . ' CarGo. All rights reserved.',
        ]);
    }

    // =========================================================================
    // PRIVATE — TOTP INTERNALS
    // =========================================================================

    /**
     * Compute a single TOTP value for the given time-step counter.
     */
    private static function computeTotp(string $secret, int $counter): string
    {
        $key    = self::base32Decode($secret);
        $time   = pack('N*', 0) . pack('N*', $counter);
        $hash   = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $otp    = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) <<  8) |
             (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::TOTP_DIGITS);

        return str_pad((string) $otp, self::TOTP_DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Decode a Base32-encoded string to binary.
     */
    private static function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret   = strtoupper(preg_replace('/\s+/', '', $secret));
        $buffer   = 0;
        $bitsLeft = 0;
        $result   = '';

        foreach (str_split($secret) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                continue;
            }
            $buffer    = ($buffer << 5) | $pos;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $result   .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $result;
    }
}