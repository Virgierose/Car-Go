<?php
require_once APP_ROOT . '/app/models/Car.php';
require_once APP_ROOT . '/app/models/Driver.php';
require_once APP_ROOT . '/app/models/Booking.php';

class BookingController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // ── STEP 1: Price Calculator ─────────────────────────────────
    public function calculator(): void {
        $cars = Car::all();
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['continue']))
            $_SESSION['booking'] = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $trip_type   = $_POST['trip_type']  ?? 'perday';
            $pickup_date = trim($_POST['pickup_date'] ?? '');
            $return_date = trim($_POST['return_date'] ?? '');
            $car_price   = (float)($_POST['car_price']  ?? 0);
            $driver_fee  = (float)($_POST['driver_fee'] ?? 0);
            $with_driver = ($_POST['with_driver'] ?? '0') === '1';
            $days        = max(1, (int)($_POST['days'] ?? 1));

            $car_total = $trip_type === 'halfday' ? $car_price * 0.6 : $car_price * $days;
            $total     = $car_total + ($with_driver ? $driver_fee * $days : 0);

            $_SESSION['booking'] = array_merge($_SESSION['booking'] ?? [], [
                'car_id'      => (int)($_POST['car_id'] ?? 0),
                'car_name'    => $_POST['car_name'] ?? '',
                'car_price'   => $car_price,
                'driver_fee'  => $driver_fee,
                'with_driver' => $with_driver,
                'trip_type'   => $trip_type,
                'pickup_date' => $pickup_date,
                'return_date' => $return_date,
                'days'        => $days,
                'total'       => $total,
            ]);
            header('Location: ' . BASE_URL . '?page=driver-selection'); exit;
        }
        render('booking/calculator', ['cars' => $cars, 'step' => 1]);
    }

    // ── STEP 2: Driver Selection ─────────────────────────────────
    public function driverSelection(): void {
        if (empty($_SESSION['booking']['car_id'])) {
            header('Location: ' . BASE_URL . '?page=price-calculator'); exit;
        }
        $availableDrivers = Driver::available();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $driver_choice = $_POST['driver_choice'] ?? 'self';
            if ($driver_choice === 'self') {
                $_SESSION['booking']['driver_id']   = null;
                $_SESSION['booking']['driver_name'] = 'Self-Drive';
                $_SESSION['booking']['with_driver'] = false;
                $_SESSION['booking']['driver_fee']  = 0;
            } else {
                $chosen_id = (int)$driver_choice;
                $driver_name = 'Driver #' . $chosen_id;
                $driver_fee  = 500;
                foreach ($availableDrivers as $d) {
                    if ((int)$d['id'] === $chosen_id) {
                        $driver_name = $d['name'];
                        $driver_fee  = isset($d['fee']) ? (float)$d['fee'] : 500;
                        break;
                    }
                }
                $_SESSION['booking']['driver_id']   = $chosen_id;
                $_SESSION['booking']['driver_name'] = $driver_name;
                $_SESSION['booking']['with_driver'] = true;
                $_SESSION['booking']['driver_fee']  = $driver_fee;
            }
            $bk        = $_SESSION['booking'];
            $days      = max(1, (int)($bk['days'] ?? 1));
            $price     = (float)($bk['car_price'] ?? 0);
            $driverFee = (float)$_SESSION['booking']['driver_fee'];
            $trip_type = $bk['trip_type'] ?? 'perday';
            $carTotal  = $trip_type === 'halfday' ? $price * 0.6 : $price * $days;
            $_SESSION['booking']['total'] = $carTotal + ($driverFee * $days);

            header('Location: ' . BASE_URL . '?page=booking-form'); exit;
        }
        render('booking/driver-selection', ['drivers' => $availableDrivers, 'step' => 2]);
    }

    // ── STEP 3: Booking Form — now saves docs, inserts as docs_pending ──
    public function form(): void {
        if (!isset($_SESSION['client_id'])) {
            $_SESSION['redirect_after_login'] = BASE_URL . '?page=booking-form';
            header('Location: ' . BASE_URL . '?page=login&next=booking-form'); exit;
        }
        if (empty($_SESSION['booking']['car_id'])) {
            header('Location: ' . BASE_URL . '?page=price-calculator'); exit;
        }
        if (!array_key_exists('driver_id', $_SESSION['booking'] ?? [])) {
            header('Location: ' . BASE_URL . '?page=driver-selection'); exit;
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log('=== BOOKING FORM POST RECEIVED ===');
            error_log('POST data: ' . json_encode($_POST));
            error_log('FILES data: ' . json_encode(array_keys($_FILES ?? [])));
            
            // DEBUG: Store POST data in session for inspection
            $_SESSION['debug_post'] = $_POST;
            $_SESSION['debug_files'] = array_keys($_FILES ?? []);
            
            $first_name        = trim($_POST['first_name']        ?? '');
            $last_name         = trim($_POST['last_name']         ?? '');
            $email             = trim($_POST['email']             ?? '');
            $phone             = trim($_POST['phone']             ?? '');
            $pickup_datetime   = trim($_POST['pickup_datetime']   ?? '');
            $return_datetime   = trim($_POST['return_datetime']   ?? '');
            $notes             = trim($_POST['notes']             ?? '');
            $destination_label = trim($_POST['destination_label'] ?? '');
            $destination_lat   = trim($_POST['destination_lat']   ?? '');
            $destination_lon   = trim($_POST['destination_lon']   ?? '');
            $distance_km       = trim($_POST['distance_km']       ?? '');

            if ($first_name === '')      $errors[] = 'First name is required.';
            if ($last_name === '')       $errors[] = 'Last name is required.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
            if ($phone === '')           $errors[] = 'Phone number is required.';
            if ($pickup_datetime === '') $errors[] = 'Pickup date & time is required.';
            if ($return_datetime === '') $errors[] = 'Return date & time is required.';
            if ($distance_km === '' || (float)$distance_km <= 0)
                $errors[] = 'Please pin your destination on the map.';
            if ($pickup_datetime && $return_datetime &&
                strtotime($return_datetime) <= strtotime($pickup_datetime))
                $errors[] = 'Return must be after pickup.';

            error_log('After field validation - Errors: ' . json_encode($errors));

            // Validate file uploads
            $allowed_types = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
            $max_size      = 5 * 1024 * 1024;
            $license_path  = null;
            $govid_path    = null;

            foreach (['license_img' => "Driver's License", 'gov_id_img' => 'Government ID'] as $field => $label) {
                if (empty($_FILES[$field]['name'])) {
                    $errors[] = $label . ' is required.';
                } elseif ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = $label . ' upload failed.';
                } elseif (!in_array($_FILES[$field]['type'], $allowed_types)) {
                    $errors[] = $label . ' must be JPG, PNG, or PDF.';
                } elseif ($_FILES[$field]['size'] > $max_size) {
                    $errors[] = $label . ' must be under 5 MB.';
                }
            }

            error_log('After file validation - Errors: ' . json_encode($errors));

            if (empty($errors)) {
                $uploadDir = APP_ROOT . '/assets/img/docs/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                foreach (['license_img' => &$license_path, 'gov_id_img' => &$govid_path] as $field => &$dest) {
                    $ext  = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                    $name = $field . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $name);
                    $dest = 'assets/img/docs/' . $name;
                }

                // ── INSERT into tbl_rental as docs_pending ─────────────
                $bk  = $_SESSION['booking'];
                $db  = Database::getInstance();
                $uid = (int)$_SESSION['client_id'];

                $car_id     = (int)($bk['car_id'] ?? 0);
                $driver_id  = (!empty($bk['driver_id'])) ? (int)$bk['driver_id'] : null;
                $pickup_dt  = $pickup_datetime;
                $return_dt  = $return_datetime;
                $pickup_loc = 'CarGo Main Branch — Bacolod City';
                $dest_addr  = $destination_label;
                $dest_lat   = !empty($destination_lat) ? (float)$destination_lat : null;
                $dest_lon   = !empty($destination_lon) ? (float)$destination_lon : null;
                $dist_km    = !empty($distance_km)     ? (float)$distance_km     : null;
                $days       = max(1, (int)($bk['days'] ?? 1));
                $total      = (float)($bk['total'] ?? 0);
                $status     = 'docs_pending';
                $docs_status= 'pending';

                // Generate unique ref
                do {
                    $ref = 'CRG-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
                    $chk = $db->prepare("SELECT booking_ref FROM tbl_rental WHERE booking_ref = ? LIMIT 1");
                    $chk->bind_param('s', $ref);
                    $chk->execute();
                    $chk->store_result();
                    $exists = $chk->num_rows > 0;
                    $chk->close();
                } while ($exists);

                $stmt = $db->prepare("
                    INSERT INTO tbl_rental
                        (client_id, car_id, driver_id,
                         rental_start, rental_end, total_days, total_amount,
                         pickup_address, dest_address, dest_lat, dest_lon, distance_km,
                         payment_method, rental_status, booking_ref, notes,
                         license_img, gov_id_img, docs_status, date_created)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
                ");

                $payment_method = 'cod'; // placeholder until admin approves
                $driver_id_bind = $driver_id;

                $stmt->bind_param(
                    'iiississdddssssssss',
                    $uid, $car_id, $driver_id_bind,
                    $pickup_dt, $return_dt, $days, $total,
                    $pickup_loc, $dest_addr, $dest_lat, $dest_lon, $dist_km,
                    $payment_method, $status, $ref, $notes,
                    $license_path, $govid_path, $docs_status
                );

                if ($stmt->execute()) {
                    $rental_id = $stmt->insert_id;
                    $stmt->close();
                    error_log('✅ Database insert successful! Rental ID: ' . $rental_id);
                    $_SESSION['booking_form'] = [
                        'ref'         => $ref,
                        'vehicle'     => $bk['car_name']          ?? '—',
                        'pickup'      => $pickup_dt,
                        'return'      => $return_dt,
                        'driver'      => $bk['driver_name']       ?? 'Self-Drive',
                        'destination' => $dest_addr,
                        'distance'    => $dist_km ? $dist_km . ' km' : '—',
                        'total'       => '₱' . number_format($total, 2),
                    ];
                    unset($_SESSION['booking']);
                    error_log('📤 Redirecting to payment: ?page=payment&rental_id=' . $rental_id);
                    header('Location: ' . BASE_URL . '?page=payment&rental_id=' . $rental_id); exit;
                }

                $errors[] = 'Could not save booking: ' . $stmt->error;
                error_log('❌ Database insert failed: ' . $stmt->error);
                $stmt->close();
            }
        }

        render('booking/form', ['step' => 3, 'errors' => $errors]);
    }

    // ── STEP 4: Payment — only accessible after admin approval ───
    public function payment(): void {
        if (!isset($_SESSION['client_id'])) {
            $_SESSION['redirect_after_login'] = BASE_URL . '?page=payment';
            header('Location: ' . BASE_URL . '?page=login&next=payment'); exit;
        }

        $rental_id = (int)($_GET['rental_id'] ?? 0);
        if (!$rental_id) {
            header('Location: ' . BASE_URL . '?page=dashboard'); exit;
        }

        $db  = Database::getInstance();
        $uid = (int)$_SESSION['client_id'];

        // Load rental — must belong to client and be approved
        $stmt = $db->prepare("
            SELECT r.*, CONCAT(cm.brand,' ',cm.model_name) AS car_name
            FROM tbl_rental r
            JOIN tbl_car ca       ON ca.car_id    = r.car_id
            JOIN tbl_car_model cm ON cm.model_id  = ca.model_id
            WHERE r.rental_id = ? AND r.client_id = ?
              AND r.rental_status = 'pending'
              AND r.docs_status   = 'approved'
            LIMIT 1
        ");
        $stmt->bind_param('ii', $rental_id, $uid);
        $stmt->execute();
        $rental = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$rental) {
            header('Location: ' . BASE_URL . '?page=dashboard&error=not_ready'); exit;
        }

        $db_error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payment_method = trim($_POST['payment_method'] ?? 'cod');
            $allowed_pm     = ['card','gcash','maya','cod'];
            if (!in_array($payment_method, $allowed_pm, true)) $payment_method = 'cod';

            $new_status = ($payment_method === 'cod') ? 'confirmed' : 'confirmed';

            $upd = $db->prepare("
                UPDATE tbl_rental
                   SET payment_method = ?, rental_status = ?
                 WHERE rental_id = ? AND client_id = ?
            ");
            $upd->bind_param('ssii', $payment_method, $new_status, $rental_id, $uid);

            if ($upd->execute()) {
                $upd->close();
                $_SESSION['booking_confirmed'] = [
                    'ref'     => $rental['booking_ref'],
                    'vehicle' => $rental['car_name'],
                    'pickup'  => $rental['rental_start'],
                    'return'  => $rental['rental_end'],
                    'driver'  => 'N/A',
                    'total'   => '₱' . number_format($rental['total_amount'], 2),
                    'paid'    => true,
                ];
                header('Location: ' . BASE_URL . '?page=confirmation'); exit;
            }
            $db_error = $upd->error;
            $upd->close();
        }

        $summary = [
            'vehicle'    => $rental['car_name'],
            'days'       => (int)$rental['total_days'],
            'rate'       => round($rental['total_amount'] / max(1, $rental['total_days']), 2),
            'driver'     => false,
            'driver_fee' => 0,
            'total'      => (float)$rental['total_amount'],
            'trip_type'  => 'perday',
        ];

        render('booking/payment', [
            'step'      => 4,
            'summary'   => $summary,
            'db_error'  => $db_error,
            'rental_id' => $rental_id,
        ]);
    }

    // ── STEP 5: Confirmation ─────────────────────────────────────
    public function confirmation(): void {
        if (!isset($_SESSION['client_id'])) {
            header('Location: ' . BASE_URL . '?page=login'); exit;
        }
        if (empty($_SESSION['booking_confirmed'])) {
            header('Location: ' . BASE_URL . '?page=browse'); exit;
        }
        $confirmed = $_SESSION['booking_confirmed'];
        unset($_SESSION['booking_confirmed']);

        render('booking/confirmation', [
            'step' => 5,
            'ref'  => $confirmed['ref'] ?? 'CRG-XXXXXX',
            'summary' => [
                'vehicle'     => $confirmed['vehicle']     ?? '—',
                'pickup'      => $confirmed['pickup']      ?? '—',
                'return'      => $confirmed['return']      ?? '—',
                'driver'      => $confirmed['driver']      ?? 'Self-Drive',
                'destination' => $confirmed['destination'] ?? '',
                'distance'    => $confirmed['distance']    ?? '',
                'total'       => $confirmed['total']       ?? '₱0.00',
            ],
        ]);
    }
}