<?php

// Database and renderAdmin() are already loaded by config.php
require_once APP_ROOT . '/app/models/Driver.php';

class AdminController {

    private $db;
    private $driverModel;

    private function requireAdmin(): void {
        if (empty($_SESSION['client_id']) || ($_SESSION['client_role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . '?page=login');
            exit;
        }
    }

    public function __construct() {
        $this->db          = Database::getInstance();
        $this->driverModel = new Driver($this->db);
    }

    // ─────────────────────────────────────────
    //  DASHBOARD
    // ─────────────────────────────────────────
    public function dashboard() {
        $this->requireAdmin();

        $carsResult   = $this->db->query("SELECT COUNT(*) as c FROM tbl_car");
        $totalCars    = (int) $carsResult->fetch_assoc()['c'];

        $bookingsResult = $this->db->query("SELECT COUNT(*) as c FROM tbl_rental");
        $totalBookings  = (int) $bookingsResult->fetch_assoc()['c'];

        $driversResult = $this->db->query("SELECT COUNT(*) as c FROM tbl_driver WHERE driver_status = 'available'");
        $totalDrivers  = (int) $driversResult->fetch_assoc()['c'];

        $revenueResult = $this->db->query("SELECT COALESCE(SUM(total_amount),0) as total FROM tbl_rental WHERE rental_status = 'completed'");
        $totalRevenue  = (float) $revenueResult->fetch_assoc()['total'];

        $rbResult = $this->db->query("
            SELECT r.*,
                   c.clnt_fname, c.clnt_lname,
                   d.drvr_fname, d.drvr_lname,
                   cm.model_name,
                   ca.plate_number
            FROM tbl_rental r
            LEFT JOIN tbl_client    c  ON r.client_id = c.client_id
            LEFT JOIN tbl_driver    d  ON r.driver_id = d.driver_id
            LEFT JOIN tbl_car       ca ON r.car_id    = ca.car_id
            LEFT JOIN tbl_car_model cm ON ca.model_id = cm.model_id
            ORDER BY r.date_created DESC
            LIMIT 10
        ");
        $recentBookings = $rbResult ? $rbResult->fetch_all(MYSQLI_ASSOC) : [];

        $carsResult2 = $this->db->query("
            SELECT ca.*, cm.model_name, cm.brand
            FROM tbl_car ca
            JOIN tbl_car_model cm ON ca.model_id = cm.model_id
            ORDER BY ca.car_dateAdded DESC
            LIMIT 8
        ");
        $cars = $carsResult2 ? $carsResult2->fetch_all(MYSQLI_ASSOC) : [];

        renderAdmin('admin/dashboard', compact(
            'totalCars', 'totalBookings', 'totalDrivers',
            'totalRevenue', 'recentBookings', 'cars'
        ));
    }

    // ─────────────────────────────────────────
    //  CARS
    // ─────────────────────────────────────────
    public function carsIndex() {
        $this->requireAdmin();

        $result = $this->db->query("
            SELECT ca.*, cm.model_name, cm.brand, cm.type, cm.price
            FROM tbl_car ca
            JOIN tbl_car_model cm ON ca.model_id = cm.model_id
            ORDER BY ca.car_dateAdded DESC
        ");
        $cars = $result->fetch_all(MYSQLI_ASSOC);

        renderAdmin('admin/cars/index', compact('cars'));
    }

    public function carCreate() {
        $this->requireAdmin();

        $result    = $this->db->query("SELECT * FROM tbl_car_model ORDER BY brand, model_name");
        $carModels = $result->fetch_all(MYSQLI_ASSOC);

        $error  = $_SESSION['auth_error']  ?? null;
        $errors = $_SESSION['auth_errors'] ?? [];
        $old    = $_SESSION['old']         ?? [];
        unset($_SESSION['auth_error'], $_SESSION['auth_errors'], $_SESSION['old']);

        renderAdmin('admin/cars/create', compact('carModels', 'error', 'errors', 'old'));
    }

    public function carStore() {
        $this->requireAdmin();

        $brand        = trim($_POST['brand']         ?? '');
        $model_name   = trim($_POST['model_name']    ?? '');
        $plate_number = strtoupper(trim($_POST['plate_number'] ?? ''));
        $year         = (int)($_POST['year']         ?? 0);
        $color        = trim($_POST['color']         ?? '');
        $status       = $_POST['status']             ?? 'available';
        $transmission = $_POST['transmission']       ?? 'Automatic';
        $fuel_type    = $_POST['fuel_type']          ?? 'Gasoline';
        $seats        = (int)($_POST['seats']        ?? 5);
        $engine       = trim($_POST['engine']        ?? '');
        $daily_rate   = (float)($_POST['daily_rate'] ?? 0);

        $errors = [];
        if (empty($brand))        $errors[] = 'Brand is required.';
        if (empty($model_name))   $errors[] = 'Model name is required.';
        if (empty($plate_number)) $errors[] = 'Plate number is required.';
        if ($year < 1990 || $year > 2030) $errors[] = 'Year must be between 1990 and 2030.';
        if (empty($color))        $errors[] = 'Color is required.';

        if (!empty($errors)) {
            $_SESSION['auth_errors'] = $errors;
            $_SESSION['old']         = $_POST;
            header('Location: ' . BASE_URL . '?page=admin-cars-create');
            exit;
        }

        $check = $this->db->prepare("SELECT car_id FROM tbl_car WHERE plate_number = ?");
        $check->bind_param('s', $plate_number);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $check->close();
            $_SESSION['auth_error'] = 'Plate number already exists.';
            $_SESSION['old']        = $_POST;
            header('Location: ' . BASE_URL . '?page=admin-cars-create');
            exit;
        }
        $check->close();

        $modelCheck = $this->db->prepare("
            SELECT model_id FROM tbl_car_model WHERE brand = ? AND model_name = ? LIMIT 1
        ");
        $modelCheck->bind_param('ss', $brand, $model_name);
        $modelCheck->execute();
        $existingModel = $modelCheck->get_result()->fetch_assoc();
        $modelCheck->close();

        if ($existingModel) {
            $model_id = $existingModel['model_id'];
        } else {
            $insertModel = $this->db->prepare("
                INSERT INTO tbl_car_model (brand, model_name, type, price) VALUES (?, ?, ?, ?)
            ");
            $type  = 'Sedan';
            $price = 0.0;
            $insertModel->bind_param('sssd', $brand, $model_name, $type, $price);
            $insertModel->execute();
            $model_id = $insertModel->insert_id;
            $insertModel->close();
        }

        $image_path = null;
        if (!empty($_FILES['car_image']['name'])) {
            $file    = $_FILES['car_image'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowed)) {
                $_SESSION['auth_error'] = 'Invalid image format. Allowed: PNG, JPG, GIF, WEBP';
                $_SESSION['old']        = $_POST;
                header('Location: ' . BASE_URL . '?page=admin-cars-create');
                exit;
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                $_SESSION['auth_error'] = 'Image size exceeds 5MB limit.';
                $_SESSION['old']        = $_POST;
                header('Location: ' . BASE_URL . '?page=admin-cars-create');
                exit;
            }
            $uploadDir = APP_ROOT . '/assets/img/cars/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename   = 'car_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                $image_path = 'assets/img/cars/' . $filename;
            }
        }

        if ($image_path) {
            $stmt = $this->db->prepare("
                INSERT INTO tbl_car (model_id, plate_number, color, year, transmission, fuel_type, seats, engine, daily_rate, status, image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ississsdsss', $model_id, $plate_number, $color, $year, $transmission, $fuel_type, $seats, $engine, $daily_rate, $status, $image_path);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO tbl_car (model_id, plate_number, color, year, transmission, fuel_type, seats, engine, daily_rate, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ississsdss', $model_id, $plate_number, $color, $year, $transmission, $fuel_type, $seats, $engine, $daily_rate, $status);
        }
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = 'Car added successfully!';
        header('Location: ' . BASE_URL . '?page=admin-cars');
        exit;
    }

    public function carEdit() {
        $this->requireAdmin();

        $id   = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare("
            SELECT ca.*, cm.model_name, cm.brand
            FROM tbl_car ca
            JOIN tbl_car_model cm ON ca.model_id = cm.model_id
            WHERE ca.car_id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $car = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$car) {
            $_SESSION['error'] = 'Car not found.';
            header('Location: ' . BASE_URL . '?page=admin-cars');
            exit;
        }

        $modelsResult = $this->db->query("SELECT * FROM tbl_car_model ORDER BY brand, model_name");
        $carModels    = $modelsResult->fetch_all(MYSQLI_ASSOC);

        renderAdmin('admin/cars/edit', compact('car', 'carModels'));
    }

    public function carUpdate() {
        $this->requireAdmin();

        $id           = (int)($_POST['car_id']      ?? 0);
        $plate_number = strtoupper(trim($_POST['plate_number'] ?? ''));
        $color        = trim($_POST['color']         ?? '');
        $year         = (int)($_POST['year']         ?? 0);
        $status       = $_POST['status']             ?? 'available';
        $transmission = $_POST['transmission']       ?? 'Automatic';
        $fuel_type    = $_POST['fuel_type']          ?? 'Gasoline';
        $seats        = (int)($_POST['seats']        ?? 5);
        $engine       = trim($_POST['engine']        ?? '');
        $daily_rate   = (float)($_POST['daily_rate'] ?? 0);

        if (empty($plate_number) || empty($color)) {
            $_SESSION['error'] = 'Please fill in all required fields.';
            header('Location: ' . BASE_URL . '?page=admin-cars-edit&id=' . $id);
            exit;
        }

        $check = $this->db->prepare("SELECT car_id FROM tbl_car WHERE plate_number = ? AND car_id != ?");
        $check->bind_param('si', $plate_number, $id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $check->close();
            $_SESSION['error'] = 'Plate number already in use by another car.';
            header('Location: ' . BASE_URL . '?page=admin-cars-edit&id=' . $id);
            exit;
        }
        $check->close();

        $stmt = $this->db->prepare("
            UPDATE tbl_car SET
                plate_number = ?, color = ?, year = ?, status = ?,
                transmission = ?, fuel_type = ?, seats = ?, engine = ?, daily_rate = ?
            WHERE car_id = ?
        ");
        $stmt->bind_param('ssissssdsi', $plate_number, $color, $year, $status, $transmission, $fuel_type, $seats, $engine, $daily_rate, $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = 'Car updated successfully!';
        header('Location: ' . BASE_URL . '?page=admin-cars');
        exit;
    }

    public function carDelete() {
        $this->requireAdmin();

        $id    = (int)($_POST['car_id'] ?? 0);
        $check = $this->db->prepare("
            SELECT COUNT(*) as c FROM tbl_rental
            WHERE car_id = ? AND rental_status IN ('pending','confirmed')
        ");
        $check->bind_param('i', $id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();
        $check->close();

        if ($row['c'] > 0) {
            $_SESSION['error'] = 'Cannot delete: this car has active bookings.';
            header('Location: ' . BASE_URL . '?page=admin-cars');
            exit;
        }

        $stmt = $this->db->prepare("DELETE FROM tbl_car WHERE car_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = 'Car deleted successfully.';
        header('Location: ' . BASE_URL . '?page=admin-cars');
        exit;
    }

    public function carStatus() {
        $this->requireAdmin();

        $id     = (int)($_POST['car_id'] ?? 0);
        $status = $_POST['status']       ?? 'available';

        $stmt = $this->db->prepare("UPDATE tbl_car SET status = ? WHERE car_id = ?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = 'Car status updated!';
        header('Location: ' . BASE_URL . '?page=admin-cars');
        exit;
    }

    // ─────────────────────────────────────────
    //  BOOKINGS  ← FIXED: now reads tbl_rental
    // ─────────────────────────────────────────
    public function bookings() {
        $this->requireAdmin();

        $activeTab = $_GET['status']    ?? 'All';
        $search    = trim($_GET['search']    ?? '');
        $dateFrom  = trim($_GET['date_from'] ?? '');
        $dateTo    = trim($_GET['date_to']   ?? '');

        $sql = "
            SELECT r.*,
                   c.clnt_fname, c.clnt_lname,
                   cm.model_name,
                   ca.plate_number,
                   d.drvr_fname, d.drvr_lname
            FROM tbl_rental r
            LEFT JOIN tbl_client    c  ON r.client_id = c.client_id
            LEFT JOIN tbl_car       ca ON r.car_id    = ca.car_id
            LEFT JOIN tbl_car_model cm ON ca.model_id = cm.model_id
            LEFT JOIN tbl_driver    d  ON r.driver_id = d.driver_id
            WHERE 1=1
        ";

        $params = [];
        $types  = '';

        if ($activeTab && $activeTab !== 'All') {
            $sql    .= " AND r.rental_status = ?";
            $types  .= 's';
            $params[] = strtolower($activeTab);
        }

        if ($search !== '') {
            $sql    .= " AND (
                c.clnt_fname    LIKE ? OR
                c.clnt_lname    LIKE ? OR
                cm.model_name   LIKE ? OR
                ca.plate_number LIKE ? OR
                d.drvr_fname    LIKE ? OR
                d.drvr_lname    LIKE ?
            )";
            $like    = '%' . $search . '%';
            $types  .= 'ssssss';
            array_push($params, $like, $like, $like, $like, $like, $like);
        }

        if ($dateFrom !== '') {
            $sql    .= " AND DATE(r.rental_start) >= ?";
            $types  .= 's';
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $sql    .= " AND DATE(r.rental_end) <= ?";
            $types  .= 's';
            $params[] = $dateTo;
        }

        $sql .= " ORDER BY r.date_created DESC";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        renderAdmin('admin/bookings/index', compact('bookings'));
    }

    // Status update handler
    public function bookingStatus() {
        $this->requireAdmin();

        $id      = (int)($_POST['booking_id'] ?? 0);
        $status  = strtolower($_POST['status'] ?? '');
        $allowed = ['pending', 'confirmed', 'cancelled', 'completed'];

        if ($id && in_array($status, $allowed, true)) {
            $stmt = $this->db->prepare("UPDATE tbl_rental SET rental_status = ? WHERE rental_id = ?");
            $stmt->bind_param('si', $status, $id);
            $stmt->execute();
            $stmt->close();
        }

        $qs = http_build_query(array_filter([
            'page'      => 'admin-bookings',
            'status'    => $_POST['current_tab'] ?? null,
            'search'    => $_POST['search']      ?? null,
            'date_from' => $_POST['date_from']   ?? null,
            'date_to'   => $_POST['date_to']     ?? null,
        ]));
        header('Location: ' . BASE_URL . '?' . $qs);
        exit;
    }

    // ─────────────────────────────────────────
    //  DRIVERS
    // ─────────────────────────────────────────
    public function drivers() {
        $this->requireAdmin();

        $search  = trim($_GET['search'] ?? '');
        $status  = trim($_GET['status'] ?? '');
        $drivers = $this->driverModel->getAll($search, $status);

        renderAdmin('admin/drivers/index', compact('drivers'));
    }

    public function driverCreate() {
        $this->requireAdmin();

        $errors = $_SESSION['driver_errors'] ?? [];
        $old    = $_SESSION['driver_old']    ?? [];
        unset($_SESSION['driver_errors'], $_SESSION['driver_old']);

        renderAdmin('admin/drivers/create', compact('errors', 'old'));
    }

    public function driverStore() {
        $this->requireAdmin();

        $data = [
            'drvr_fname'        => trim($_POST['drvr_fname']        ?? ''),
            'drvr_lname'        => trim($_POST['drvr_lname']        ?? ''),
            'drvr_mname'        => trim($_POST['drvr_mname']        ?? ''),
            'drvr_license_no'   => trim($_POST['drvr_license_no']   ?? ''),
            'drvr_phone_number' => trim($_POST['drvr_phone_number'] ?? ''),
            'rate_per_day'      => trim($_POST['rate_per_day']      ?? '0'),
            'driver_status'     => $_POST['driver_status']          ?? 'available',
        ];

        $errors = [];
        if (empty($data['drvr_fname']))        $errors[] = 'First name is required.';
        if (empty($data['drvr_lname']))        $errors[] = 'Last name is required.';
        if (empty($data['drvr_license_no']))   $errors[] = 'License number is required.';
        if (empty($data['drvr_phone_number'])) $errors[] = 'Phone number is required.';
        if (!is_numeric($data['rate_per_day']) || (float)$data['rate_per_day'] < 0) {
            $errors[] = 'Daily rate must be a valid number (0 or above).';
        }

        if (empty($errors) && $this->driverModel->licenseExists($data['drvr_license_no'])) {
            $errors[] = 'A driver with that license number already exists.';
        }

        if (!empty($errors)) {
            $_SESSION['driver_errors'] = $errors;
            $_SESSION['driver_old']    = $data;
            header('Location: ' . BASE_URL . '?page=admin-drivers-create');
            exit;
        }

        $this->driverModel->create($data);
        $_SESSION['success'] = 'Driver registered successfully!';
        header('Location: ' . BASE_URL . '?page=admin-drivers');
        exit;
    }

    public function driverEdit() {
        $this->requireAdmin();

        $id     = (int)($_GET['id'] ?? 0);
        $driver = $this->driverModel->getById($id);

        if (!$driver) {
            $_SESSION['error'] = 'Driver not found.';
            header('Location: ' . BASE_URL . '?page=admin-drivers');
            exit;
        }

        $errors  = $_SESSION['driver_errors'] ?? [];
        $old     = $_SESSION['driver_old']    ?? [];
        $success = $_SESSION['success']       ?? null;
        unset($_SESSION['driver_errors'], $_SESSION['driver_old'], $_SESSION['success']);

        renderAdmin('admin/drivers/edit', compact('driver', 'errors', 'old', 'success'));
    }

    public function driverUpdate() {
        $this->requireAdmin();

        $id   = (int)($_GET['id'] ?? $_POST['driver_id'] ?? 0);
        $data = [
            'drvr_fname'        => trim($_POST['drvr_fname']        ?? ''),
            'drvr_lname'        => trim($_POST['drvr_lname']        ?? ''),
            'drvr_mname'        => trim($_POST['drvr_mname']        ?? ''),
            'drvr_license_no'   => trim($_POST['drvr_license_no']   ?? ''),
            'drvr_phone_number' => trim($_POST['drvr_phone_number'] ?? ''),
            'rate_per_day'      => trim($_POST['rate_per_day']      ?? '0'),
            'driver_status'     => $_POST['driver_status']          ?? 'available',
        ];

        $errors = [];
        if (empty($data['drvr_fname']))        $errors[] = 'First name is required.';
        if (empty($data['drvr_lname']))        $errors[] = 'Last name is required.';
        if (empty($data['drvr_license_no']))   $errors[] = 'License number is required.';
        if (empty($data['drvr_phone_number'])) $errors[] = 'Phone number is required.';
        if (!is_numeric($data['rate_per_day']) || (float)$data['rate_per_day'] < 0) {
            $errors[] = 'Daily rate must be a valid number (0 or above).';
        }

        if (empty($errors) && $this->driverModel->licenseExists($data['drvr_license_no'], $id)) {
            $errors[] = 'Another driver with that license number already exists.';
        }

        if (!empty($errors)) {
            $_SESSION['driver_errors'] = $errors;
            $_SESSION['driver_old']    = $data;
            header('Location: ' . BASE_URL . '?page=admin-drivers-edit&id=' . $id);
            exit;
        }

        $this->driverModel->update($id, $data);
        $_SESSION['success'] = 'Driver updated successfully!';
        header('Location: ' . BASE_URL . '?page=admin-drivers-edit&id=' . $id);
        exit;
    }

    public function driverDelete() {
        $this->requireAdmin();

        $id    = (int)($_POST['id'] ?? 0);
        $check = $this->db->prepare("
            SELECT COUNT(*) as c FROM tbl_rental
            WHERE driver_id = ? AND rental_status IN ('pending','confirmed')
        ");
        $check->bind_param('i', $id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();
        $check->close();

        if ($row['c'] > 0) {
            $_SESSION['error'] = 'Cannot remove: this driver has active bookings.';
            header('Location: ' . BASE_URL . '?page=admin-drivers');
            exit;
        }

        $this->driverModel->delete($id);
        $_SESSION['success'] = 'Driver removed successfully.';
        header('Location: ' . BASE_URL . '?page=admin-drivers');
        exit;
    }

    // ─────────────────────────────────────────
    //  CLIENTS
    // ─────────────────────────────────────────
    public function clients() {
        $this->requireAdmin();

        $result = $this->db->query("
            SELECT c.*, COUNT(r.rental_id) as total_bookings
            FROM tbl_client c
            LEFT JOIN tbl_rental r ON c.client_id = r.client_id
            WHERE c.role = 'client'
            GROUP BY c.client_id
            ORDER BY c.client_dateAdded DESC
        ");
        $clients = $result->fetch_all(MYSQLI_ASSOC);

        renderAdmin('admin/clients/index', compact('clients'));
    }

    // ─────────────────────────────────────────
    //  PAYMENTS
    // ─────────────────────────────────────────
    public function payments() {
        $this->requireAdmin();

        $result = $this->db->query("
            SELECT p.*, c.clnt_fname, c.clnt_lname, b.pickup_date, b.return_date
            FROM tbl_payment p
            LEFT JOIN tbl_booking b ON p.booking_id = b.booking_id
            LEFT JOIN tbl_client  c ON b.client_id  = c.client_id
            ORDER BY p.payment_dateAdded DESC
        ");
        $payments = $result->fetch_all(MYSQLI_ASSOC);

        renderAdmin('admin/payments/index', compact('payments'));
    }

    // ─────────────────────────────────────────
    //  MESSAGES
    // ─────────────────────────────────────────
    public function messages() {
        $this->requireAdmin();

        $result = $this->db->query("
            SELECT contact_id as message_id, name as sender_name,
                   email, subject, message, is_read,
                   contact_dateAdded as created_at
            FROM tbl_contact
            ORDER BY contact_dateAdded DESC
        ");
        $messages = $result->fetch_all(MYSQLI_ASSOC);

        $unreadResult = $this->db->query("SELECT COUNT(*) as count FROM tbl_contact WHERE is_read = 0");
        $unreadCount  = (int) $unreadResult->fetch_assoc()['count'];

        $currentMessage = null;
        if (!empty($_GET['id'])) {
            $msgId     = (int)$_GET['id'];
            $msgResult = $this->db->prepare("
                SELECT contact_id as message_id, name as sender_name,
                       email, subject, message, is_read,
                       contact_dateAdded as created_at
                FROM tbl_contact WHERE contact_id = ?
            ");
            $msgResult->bind_param('i', $msgId);
            $msgResult->execute();
            $currentMessage = $msgResult->get_result()->fetch_assoc();
            $msgResult->close();

            if ($currentMessage && !$currentMessage['is_read']) {
                $upd = $this->db->prepare("UPDATE tbl_contact SET is_read = 1 WHERE contact_id = ?");
                $upd->bind_param('i', $msgId);
                $upd->execute();
                $upd->close();
            }
        }

        renderAdmin('admin/messages/index', compact('messages', 'unreadCount', 'currentMessage'));
    }

    public function messageRead() {
        $this->requireAdmin();

        $msgId = (int)($_GET['id'] ?? 0);
        if (!$msgId) {
            http_response_code(400);
            die(json_encode(['error' => 'Invalid message ID']));
        }

        $msgResult = $this->db->prepare("
            SELECT contact_id as message_id, name as sender_name,
                   email, subject, message, is_read,
                   contact_dateAdded as created_at
            FROM tbl_contact WHERE contact_id = ?
        ");
        $msgResult->bind_param('i', $msgId);
        $msgResult->execute();
        $currentMessage = $msgResult->get_result()->fetch_assoc();
        $msgResult->close();

        if (!$currentMessage) {
            http_response_code(404);
            die(json_encode(['error' => 'Message not found']));
        }

        if (!$currentMessage['is_read']) {
            $upd = $this->db->prepare("UPDATE tbl_contact SET is_read = 1 WHERE contact_id = ?");
            $upd->bind_param('i', $msgId);
            $upd->execute();
            $upd->close();
        }

        header('Content-Type: application/json');
        echo json_encode($currentMessage);
        exit;
    }

    public function messageDelete() {
        $this->requireAdmin();

        $msgId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if (!$msgId) {
            $_SESSION['error'] = 'Invalid message ID';
            header('Location: ' . BASE_URL . '?page=admin-messages');
            exit;
        }

        $stmt = $this->db->prepare("DELETE FROM tbl_contact WHERE contact_id = ?");
        $stmt->bind_param('i', $msgId);
        $_SESSION[$stmt->execute() ? 'success' : 'error'] = $stmt->execute()
            ? 'Message deleted successfully'
            : 'Failed to delete message';
        $stmt->close();

        header('Location: ' . BASE_URL . '?page=admin-messages');
        exit;
    }

    public function messageReply() {
        $this->requireAdmin();

        $msgId        = (int)($_POST['id']           ?? 0);
        $replyMessage = trim($_POST['reply_message'] ?? '');

        if (!$msgId || empty($replyMessage)) {
            $_SESSION['error'] = 'Invalid reply data';
            header('Location: ' . BASE_URL . '?page=admin-messages&id=' . $msgId);
            exit;
        }

        $msgResult = $this->db->prepare("
            SELECT email, name as sender_name, subject FROM tbl_contact WHERE contact_id = ?
        ");
        $msgResult->bind_param('i', $msgId);
        $msgResult->execute();
        $message = $msgResult->get_result()->fetch_assoc();
        $msgResult->close();

        if (!$message) {
            $_SESSION['error'] = 'Message not found';
            header('Location: ' . BASE_URL . '?page=admin-messages');
            exit;
        }

        require_once APP_ROOT . '/vendor/autoload.php';
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USER') ?: 'your-email@gmail.com';
            $mail->Password   = getenv('SMTP_PASS') ?: 'your-app-password';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->setFrom('noreply@cargo.com', 'CARGO Admin');
            $mail->addAddress($message['email'], $message['sender_name']);
            $mail->isHTML(true);
            $mail->Subject = 'Re: ' . $message['subject'];
            $mail->Body    = nl2br(htmlspecialchars($replyMessage));
            $mail->AltBody = htmlspecialchars($replyMessage);
            $mail->send();
            $_SESSION['success'] = 'Reply sent successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to send reply: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '?page=admin-messages&id=' . $msgId);
        exit;
    }

    // ─────────────────────────────────────────
    //  ROUTING WRAPPERS
    // ─────────────────────────────────────────
    public function index() { $this->dashboard(); }
    public function cars()  { $this->carsIndex(); }
}