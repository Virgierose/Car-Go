<?php

require_once APP_ROOT . '/app/models/Car.php';
require_once APP_ROOT . '/app/models/Driver.php';
require_once APP_ROOT . '/app/models/Booking.php';

class BookingController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // ── STEP 1: Price Calculator ───────────────────────────────────
    public function calculator(): void {
        $cars = Car::all();

        // Clear old booking session when starting fresh (GET without ?continue)
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['continue'])) {
            $_SESSION['booking'] = [];
        }

        // Handle POST: save calculator selections to session, advance to step 2
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $trip_type   = $_POST['trip_type']  ?? 'perday';
            $pickup_date = trim($_POST['pickup_date'] ?? '');
            $return_date = trim($_POST['return_date'] ?? '');
            $car_price   = (float) ($_POST['car_price']  ?? 0);
            $driver_fee  = (float) ($_POST['driver_fee'] ?? 0);
            $with_driver = ($_POST['with_driver'] ?? '0') === '1';
            $days        = max(1, (int) ($_POST['days'] ?? 1));

            // Recalculate total server-side (never trust client-submitted totals)
            if ($trip_type === 'halfday') {
                $car_total = $car_price * 0.6;
            } else {
                $car_total = $car_price * $days;
            }
            $total = $car_total + ($with_driver ? $driver_fee * $days : 0);

            $_SESSION['booking']['car_id']      = (int)   ($_POST['car_id']   ?? 0);
            $_SESSION['booking']['car_name']    =          $_POST['car_name'] ?? '';
            $_SESSION['booking']['car_price']   = $car_price;
            $_SESSION['booking']['driver_fee']  = $driver_fee;
            $_SESSION['booking']['with_driver'] = $with_driver;
            $_SESSION['booking']['trip_type']   = $trip_type;
            $_SESSION['booking']['pickup_date'] = $pickup_date;
            $_SESSION['booking']['return_date'] = $return_date;
            $_SESSION['booking']['days']        = $days;
            $_SESSION['booking']['total']       = $total;

            header('Location: ' . BASE_URL . '?page=driver-selection');
            exit;
        }

        render('booking/calculator', [
            'cars' => $cars,
            'step' => 1,
        ]);
    }

    // ── STEP 2: Driver Selection ───────────────────────────────────
    public function driverSelection(): void {
        // Guard: must have car selected from step 1
        if (empty($_SESSION['booking']['car_id'])) {
            header('Location: ' . BASE_URL . '?page=price-calculator');
            exit;
        }

        $availableDrivers = Driver::available();

        // Handle POST: save driver choice to session, advance to step 3
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $driver_choice = $_POST['driver_choice'] ?? 'self';

            if ($driver_choice === 'self') {
                $_SESSION['booking']['driver_id']   = null;
                $_SESSION['booking']['driver_name'] = 'Self-Drive';
                $_SESSION['booking']['with_driver'] = false;
                $_SESSION['booking']['driver_fee']  = 0;
            } else {
                $chosen_id   = (int) $driver_choice;
                $driver_name = 'Driver #' . $chosen_id;
                $driver_fee  = 500; // default

                // Look up the driver's name and fee from the available list
                foreach ($availableDrivers as $d) {
                    if ((int)$d['id'] === $chosen_id) {
                        $driver_name = $d['name'];
                        // Use DB fee if available, otherwise fall back to 500
                        $driver_fee  = isset($d['fee']) ? (float)$d['fee'] : 500;
                        break;
                    }
                }

                $_SESSION['booking']['driver_id']   = $chosen_id;
                $_SESSION['booking']['driver_name'] = $driver_name;
                $_SESSION['booking']['with_driver'] = true;
                $_SESSION['booking']['driver_fee']  = $driver_fee;
            }

            // Recalculate total with updated driver fee (server-side)
            $bk        = $_SESSION['booking'];
            $days      = max(1, (int)   ($bk['days']      ?? 1));
            $price     = (float) ($bk['car_price'] ?? 0);
            $driverFee = (float) ($_SESSION['booking']['driver_fee']);
            $trip_type = $bk['trip_type'] ?? 'perday';

            if ($trip_type === 'halfday') {
                $carTotal = $price * 0.6;
            } else {
                $carTotal = $price * $days;
            }
            $_SESSION['booking']['total'] = $carTotal + ($driverFee * $days);

            header('Location: ' . BASE_URL . '?page=booking-form');
            exit;
        }

        render('booking/driver-selection', [
            'drivers' => $availableDrivers,
            'step'    => 2,
        ]);
    }

    // ── STEP 3: Booking Form (Personal + Pickup Details) ──────────
    public function form(): void {
        // Auth gate
        if (!isset($_SESSION['client_id'])) {
            $_SESSION['redirect_after_login'] = BASE_URL . '?page=booking-form';
            header('Location: ' . BASE_URL . '?page=login&next=booking-form');
            exit;
        }

        // Guard: must have car selected from step 1
        if (empty($_SESSION['booking']['car_id'])) {
            header('Location: ' . BASE_URL . '?page=price-calculator');
            exit;
        }

        // Guard: must have gone through driver selection (step 2)
        if (!array_key_exists('driver_id', $_SESSION['booking'] ?? [])) {
            header('Location: ' . BASE_URL . '?page=driver-selection');
            exit;
        }

        $errors = [];

        // Handle POST: validate, save personal + pickup info, advance to step 4
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $first_name      = trim($_POST['first_name']      ?? '');
            $last_name       = trim($_POST['last_name']       ?? '');
            $email           = trim($_POST['email']           ?? '');
            $phone           = trim($_POST['phone']           ?? '');
            $pickup_location = trim($_POST['pickup_location'] ?? '');
            $return_location = trim($_POST['return_location'] ?? '');
            $pickup_datetime = trim($_POST['pickup_datetime'] ?? '');
            $return_datetime = trim($_POST['return_datetime'] ?? '');
            $notes           = trim($_POST['notes']           ?? '');

            // Basic validation
            if ($first_name === '')      $errors[] = 'First name is required.';
            if ($last_name === '')       $errors[] = 'Last name is required.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
            if ($phone === '')           $errors[] = 'Phone number is required.';
            if ($pickup_location === '') $errors[] = 'Pickup location is required.';
            if ($pickup_datetime === '') $errors[] = 'Pickup date & time is required.';
            if ($return_datetime === '') $errors[] = 'Return date & time is required.';

            // Validate return is after pickup
            if ($pickup_datetime !== '' && $return_datetime !== '') {
                if (strtotime($return_datetime) <= strtotime($pickup_datetime)) {
                    $errors[] = 'Return date/time must be after the pickup date/time.';
                }
            }

            if (empty($errors)) {
                $_SESSION['booking']['first_name']      = $first_name;
                $_SESSION['booking']['last_name']       = $last_name;
                $_SESSION['booking']['email']           = $email;
                $_SESSION['booking']['phone']           = $phone;
                $_SESSION['booking']['pickup_location'] = $pickup_location;
                $_SESSION['booking']['return_location'] = $return_location !== '' ? $return_location : $pickup_location;
                $_SESSION['booking']['pickup_datetime'] = $pickup_datetime;
                $_SESSION['booking']['return_datetime'] = $return_datetime;
                $_SESSION['booking']['notes']           = $notes;

                header('Location: ' . BASE_URL . '?page=payment');
                exit;
            }
        }

        render('booking/form', [
            'step'   => 3,
            'errors' => $errors,
        ]);
    }

    // ── STEP 4: Payment ────────────────────────────────────────────
    public function payment(): void {
        // Auth gate
        if (!isset($_SESSION['client_id'])) {
            $_SESSION['redirect_after_login'] = BASE_URL . '?page=payment';
            header('Location: ' . BASE_URL . '?page=login&next=payment');
            exit;
        }

        // Guard: must have personal info from step 3
        if (empty($_SESSION['booking']['first_name'])) {
            header('Location: ' . BASE_URL . '?page=booking-form');
            exit;
        }

        $bk       = $_SESSION['booking'];
        $db_error = null;

        // Handle POST: insert booking into DB, build confirmation session, redirect
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payment_method = trim($_POST['payment_method'] ?? 'cod');
            $allowed_pm     = ['card', 'gcash', 'maya', 'cod'];
            if (!in_array($payment_method, $allowed_pm, true)) {
                $payment_method = 'cod';
            }

            $db         = Database::getInstance();
            $uid        = (int) $_SESSION['client_id'];
            $car_id     = (int) ($bk['car_id']  ?? 0);

            // driver_id must be NULL (not 0) when self-drive
            $driver_id  = (!empty($bk['driver_id']) && $bk['driver_id'] !== null)
                          ? (int) $bk['driver_id']
                          : null;

            $pickup_dt  = $bk['pickup_datetime']  ?? '';
            $return_dt  = $bk['return_datetime']  ?? '';
            $pickup_loc = $bk['pickup_location']  ?? '';
            $days       = max(1, (int)   ($bk['days']  ?? 1));
            $total      = (float) ($bk['total']   ?? 0);
            $notes      = $bk['notes']            ?? '';

            // COD = pending (needs manual confirmation), anything else = confirmed
            $status = ($payment_method === 'cod') ? 'pending' : 'confirmed';

            // Generate unique reference code
            do {
                $ref = 'CRG-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
                // Check uniqueness (optional but good practice)
                $chk = $db->prepare("SELECT booking_ref FROM tbl_rental WHERE booking_ref = ? LIMIT 1");
                $chk->bind_param('s', $ref);
                $chk->execute();
                $chk->store_result();
                $exists = $chk->num_rows > 0;
                $chk->close();
            } while ($exists);

            /*
             * tbl_rental columns (13 total, date_created uses NOW()):
             *   client_id      → i
             *   car_id         → i
             *   driver_id      → i  (nullable — use bind_param with null)
             *   rental_start   → s
             *   rental_end     → s
             *   total_days     → i
             *   total_amount   → d
             *   pickup_address → s
             *   payment_method → s
             *   rental_status  → s
             *   booking_ref    → s
             *   notes          → s
             *   date_created   → NOW()  (no placeholder)
             *
             * Type string: i i i s s i d s s s s s  → 'iiissidsssss' (12 chars, 12 placeholders)
             */
            $stmt = $db->prepare("
                INSERT INTO tbl_rental
                    (client_id, car_id, driver_id,
                     rental_start, rental_end, total_days, total_amount,
                     pickup_address,
                     payment_method, rental_status, booking_ref, notes, date_created)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            if (!$stmt) {
                $db_error = 'Query prepare failed: ' . $db->error;
            } else {
                // MySQLi does not natively bind NULL through bind_param for ints.
                // Use a local variable that can be set to null.
                $driver_id_bind = $driver_id; // null or int

                $stmt->bind_param(
                    'iiissidsssss',
                    $uid,
                    $car_id,
                    $driver_id_bind,
                    $pickup_dt,
                    $return_dt,
                    $days,
                    $total,
                    $pickup_loc,
                    $payment_method,
                    $status,
                    $ref,
                    $notes
                );

                if ($stmt->execute()) {
                    $stmt->close();

                    // Store confirmation data for the next page
                    $_SESSION['booking_confirmed'] = [
                        'ref'     => $ref,
                        'vehicle' => $bk['car_name']    ?? '—',
                        'pickup'  => $pickup_dt,
                        'return'  => $return_dt,
                        'driver'  => $bk['driver_name'] ?? 'Self-Drive',
                        'total'   => '₱' . number_format($total, 2),
                    ];

                    // Clear the in-progress booking session
                    unset($_SESSION['booking']);

                    header('Location: ' . BASE_URL . '?page=confirmation');
                    exit;
                }

                // DB insert failed — fall through to re-render with error
                $db_error = 'Booking could not be saved. Please try again. (' . $stmt->error . ')';
                $stmt->close();
            }
        }

        // Build order summary for display
        $summary = [
            'vehicle'    => $bk['car_name']    ?? '—',
            'days'       => max(1, (int)   ($bk['days']       ?? 1)),
            'rate'       => (float) ($bk['car_price']  ?? 0),
            'driver'     => $bk['with_driver']  ?? false,
            'driver_fee' => (float) ($bk['driver_fee'] ?? 0),
            'total'      => (float) ($bk['total']      ?? 0),
            'trip_type'  => $bk['trip_type']    ?? 'perday',
        ];

        render('booking/payment', [
            'step'     => 4,
            'summary'  => $summary,
            'db_error' => $db_error,
        ]);
    }

    // ── STEP 5: Confirmation ───────────────────────────────────────
    public function confirmation(): void {
        // Auth gate
        if (!isset($_SESSION['client_id'])) {
            header('Location: ' . BASE_URL . '?page=login');
            exit;
        }

        // Guard: must arrive here from a completed payment
        if (empty($_SESSION['booking_confirmed'])) {
            header('Location: ' . BASE_URL . '?page=browse');
            exit;
        }

        // Capture and clear — prevents stale data on refresh
        $confirmed = $_SESSION['booking_confirmed'];
        unset($_SESSION['booking_confirmed']);

        render('booking/confirmation', [
            'step' => 5,
            'ref'  => $confirmed['ref'] ?? 'CRG-XXXXXX',
            'summary' => [
                'vehicle' => $confirmed['vehicle'] ?? '—',
                'pickup'  => $confirmed['pickup']  ?? '—',
                'return'  => $confirmed['return']  ?? '—',
                'driver'  => $confirmed['driver']  ?? 'Self-Drive',
                'total'   => $confirmed['total']   ?? '₱0.00',
            ],
        ]);
    }
}