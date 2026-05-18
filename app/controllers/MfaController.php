<?php

require_once APP_ROOT . '/config/Database.php';

if (!function_exists('render')) {
    function render(string $view, array $data = []): void {
        extract($data, EXTR_SKIP);
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View not found: <strong>{$view}</strong>");
        }
        require $viewFile;
    }
}

if (!class_exists('MfaController')) :
class MfaController
{
    // ── GUARD ────────────────────────────────────────────────────────────────
    private function requirePending(): int
    {
        if (empty($_SESSION['mfa_pending_client_id'])) {
            header('Location: ' . BASE_URL . '?page=login');
            exit;
        }
        return (int) $_SESSION['mfa_pending_client_id'];
    }

    // ── OTP ENTRY — routes: GET /mfa-otp, POST /mfa-otp ─────────────────────
    public function otpEntry(): void
    {
        $clientId = $this->requirePending();
        $role     = $_SESSION['mfa_pending_role'] ?? 'client';
        $db       = Database::getInstance();

        $stmt = $db->prepare(
            "SELECT client_id, clnt_fname, clnt_lname, email, mfa_secret, role
             FROM tbl_client WHERE client_id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $clientId);
        $stmt->execute();
        $client = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$client) {
            session_destroy();
            header('Location: ' . BASE_URL . '?page=login');
            exit;
        }

        // ── ADMIN: TOTP only, never email OTP ────────────────────────────────
        if ($client['role'] === 'admin') {
            if (empty($client['mfa_secret'])) {
                // First-time admin → force TOTP setup
                header('Location: ' . BASE_URL . '?page=mfa-setup');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $error = $_SESSION['mfa_error'] ?? null;
                unset($_SESSION['mfa_error']);
                render('auth/admin-mfa-otp', compact('client', 'error'));
                return;
            }

            // POST: verify TOTP
            $submitted = trim($_POST['totp_code'] ?? '');
            if (MfaHelper::verifyTotp($client['mfa_secret'], $submitted)) {
                $this->finaliseLogin($client);
            } else {
                $_SESSION['mfa_error'] = 'Invalid code. Please try again.';
                $error = $_SESSION['mfa_error'];
                unset($_SESSION['mfa_error']);
                render('auth/admin-mfa-otp', compact('client', 'error'));
            }
            return;
        }

        // ── CLIENT: email OTP or first-time TOTP setup ────────────────────────
        if (empty($client['mfa_secret'])) {
            header('Location: ' . BASE_URL . '?page=mfa-setup');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                MfaHelper::sendEmailOtp($db, $clientId, $client['email']);
                $_SESSION['mfa_email_sent'] = true;
            } catch (RuntimeException $e) {
                $_SESSION['mfa_error'] = 'Failed to send verification email. Please try again.';
            }
            $this->renderOtpForm($client['email']);
            return;
        }

        // POST: verify email OTP
        $submitted = trim($_POST['otp_code'] ?? '');
        if (MfaHelper::verifyEmailOtp($db, $clientId, $submitted)) {
            $this->finaliseLogin($client);
        } else {
            $_SESSION['mfa_error'] = 'Incorrect or expired code. Please try again.';
            $this->renderOtpForm($client['email']);
        }
    }

    // ── TOTP SETUP (first-time QR scan) — GET /mfa-setup ─────────────────────
    public function totpSetup(): void
    {
        $clientId = $this->requirePending();
        $db       = Database::getInstance();

        $stmt = $db->prepare(
            "SELECT client_id, clnt_fname, clnt_lname, email, mfa_secret, role
             FROM tbl_client WHERE client_id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $clientId);
        $stmt->execute();
        $client = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (empty($_SESSION['mfa_tmp_secret'])) {
            $_SESSION['mfa_tmp_secret'] = MfaHelper::generateSecret();
        }
        $secret = $_SESSION['mfa_tmp_secret'];
        $qrUri  = MfaHelper::getQrCodeDataUri($secret, $client['email']);
        $error  = $_SESSION['mfa_error'] ?? null;
        unset($_SESSION['mfa_error']);

        render('auth/mfa-setup', compact('qrUri', 'secret', 'error', 'client'));
    }

    // ── TOTP VERIFY (after QR scan) — POST /mfa-verify ───────────────────────
    public function totpVerify(): void
    {
        $clientId = $this->requirePending();

        if (empty($_SESSION['mfa_tmp_secret']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?page=mfa-setup');
            exit;
        }

        $db        = Database::getInstance();
        $secret    = $_SESSION['mfa_tmp_secret'];
        $submitted = trim($_POST['totp_code'] ?? '');

        if (!MfaHelper::verifyTotp($secret, $submitted)) {
            $_SESSION['mfa_error'] = 'Invalid code — make sure your device time is correct and try again.';
            header('Location: ' . BASE_URL . '?page=mfa-setup');
            exit;
        }

        MfaHelper::saveSecret($db, $clientId, $secret);
        unset($_SESSION['mfa_tmp_secret']);

        $stmt = $db->prepare(
            "SELECT client_id, clnt_fname, clnt_lname, email, role
             FROM tbl_client WHERE client_id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $clientId);
        $stmt->execute();
        $client = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->finaliseLogin($client);
    }

    // ── FINALISE LOGIN ────────────────────────────────────────────────────────
    private function finaliseLogin(array $client): void
    {
        unset(
            $_SESSION['mfa_pending_client_id'],
            $_SESSION['mfa_pending_role'],
            $_SESSION['mfa_email_sent'],
            $_SESSION['mfa_error']
        );

        session_regenerate_id(true);
        $_SESSION['client_id']    = $client['client_id'];
        $_SESSION['client_fname'] = $client['clnt_fname'];
        $_SESSION['client_lname'] = $client['clnt_lname'];
        $_SESSION['client_email'] = $client['email'];
        $_SESSION['client_role']  = $client['role'];

        if ($client['role'] === 'admin') {
            header('Location: ' . BASE_URL . '?page=admin');
        } else {
            $redirect = $_SESSION['redirect_after_login'] ?? null;
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . ($redirect ?: BASE_URL . '?page=dashboard'));
        }
        exit;
    }

    // ── RENDER CLIENT EMAIL OTP FORM ──────────────────────────────────────────
    private function renderOtpForm(string $email): void
    {
        $error = $_SESSION['mfa_error']      ?? null;
        $sent  = $_SESSION['mfa_email_sent'] ?? false;
        unset($_SESSION['mfa_error'], $_SESSION['mfa_email_sent']);
        render('auth/mfa-otp', compact('email', 'error', 'sent'));
    }
}
endif;