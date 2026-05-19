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

class AdminController {
    // ── GUARD ────────────────────────────────────────────────────────────────
    private function requireAdmin(): void {
        if (empty($_SESSION['client_id']) || ($_SESSION['client_role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . '?page=login');
            exit;
        }
    }
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ─────────────────────────────────────────
    //  DASHBOARD
    // ─────────────────────────────────────────
    public function dashboard() {
        $this->requireAdmin();
        
        $carsResult    = $this->db->query("SELECT COUNT(*) as c FROM tbl_car");
        $totalCars     = (int) $carsResult->fetch_assoc()['c'];
        
        $bookingsResult = $this->db->query("SELECT COUNT(*) as c FROM tbl_booking");
        $totalBookings = (int) $bookingsResult->fetch_assoc()['c'];
        
        $driversResult = $this->db->query("SELECT COUNT(*) as c FROM tbl_driver WHERE driver_status = 'available'");
        $totalDrivers  = (int) $driversResult->fetch_assoc()['c'];
        
        $revenueResult = $this->db->query("SELECT COALESCE(SUM(amount),0) as total FROM tbl_payment WHERE payment_status = 'paid'");
        $totalRevenue  = (float) $revenueResult->fetch_assoc()['total'];

        $rbResult = $this->db->query("
            SELECT b.*, 
                   c.clnt_fname, c.clnt_lname,
                   d.drvr_fname, d.drvr_lname,
                   cm.model_name,
                   ca.plate_number
            FROM tbl_booking b
            JOIN tbl_client c  ON b.client_id = c.client_id
            JOIN tbl_driver d  ON b.driver_id = d.driver_id
            JOIN tbl_car    ca ON b.car_id    = ca.car_id
            JOIN tbl_car_model cm ON ca.model_id = cm.model_id
            ORDER BY b.booking_dateAdded DESC
            LIMIT 10
        ");
        $recentBookings = $rbResult->fetch_all(MYSQLI_ASSOC);

        $carsResult = $this->db->query("
            SELECT ca.*, cm.model_name, cm.brand
            FROM tbl_car ca
            JOIN tbl_car_model cm ON ca.model_id = cm.model_id
            ORDER BY ca.car_dateAdded DESC
            LIMIT 8
        ");
        $cars = $carsResult->fetch_all(MYSQLI_ASSOC);

        renderAdmin('admin/dashboard', compact('totalCars', 'totalBookings', 'totalDrivers', 'totalRevenue', 'recentBookings', 'cars'));
    }

    // ─────────────────────────────────────────
    //  LIST ALL CARS
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

    // ─────────────────────────────────────────
    //  CREATE CAR FORM
    // ─────────────────────────────────────────
    public function carCreate() {
        $this->requireAdmin();
        
        $result = $this->db->query("
            SELECT * FROM tbl_car_model ORDER BY brand, model_name
        ");
        $carModels = $result->fetch_all(MYSQLI_ASSOC);

        $error   = $_SESSION['auth_error']   ?? null;
        $errors  = $_SESSION['auth_errors']  ?? [];
        $old     = $_SESSION['old']          ?? [];
        unset($_SESSION['auth_error'], $_SESSION['auth_errors'], $_SESSION['old']);

        renderAdmin('admin/cars/create', compact('carModels', 'error', 'errors', 'old'));
    }

    // ─────────────────────────────────────────
    //  STORE NEW CAR
    // ─────────────────────────────────────────
    public function carStore() {
        $this->requireAdmin();
        
        $brand        = trim($_POST['brand'] ?? '');
        $model_name   = trim($_POST['model_name'] ?? '');
        $plate_number = strtoupper(trim($_POST['plate_number'] ?? ''));
        $year         = (int)($_POST['year'] ?? 0);
        $color        = trim($_POST['color'] ?? '');
        $status       = $_POST['status'] ?? 'available';
        $transmission = $_POST['transmission'] ?? 'Automatic';
        $fuel_type    = $_POST['fuel_type'] ?? 'Gasoline';
        $seats        = (int)($_POST['seats'] ?? 5);
        $engine       = trim($_POST['engine'] ?? '');
        $daily_rate   = (float)($_POST['daily_rate'] ?? 0);

        $errors = [];
        if (empty($brand)) $errors[] = 'Brand is required.';
        if (empty($model_name)) $errors[] = 'Model name is required.';
        if (empty($plate_number)) $errors[] = 'Plate number is required.';
        if (empty($year) || $year < 1990 || $year > 2030) $errors[] = 'Year must be between 1990 and 2030.';
        if (empty($color)) $errors[] = 'Color is required.';

        if (!empty($errors)) {
            $_SESSION['auth_errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . '?page=admin-cars-create');
            exit;
        }

        // Check if plate number already exists
        $check = $this->db->prepare("SELECT car_id FROM tbl_car WHERE plate_number = ?");
        $check->bind_param('s', $plate_number);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $check->close();
            $_SESSION['auth_error'] = 'Plate number already exists.';
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . '?page=admin-cars-create');
            exit;
        }
        $check->close();

        // Find or create car model
        $modelCheck = $this->db->prepare("
            SELECT model_id FROM tbl_car_model WHERE brand = ? AND model_name = ? LIMIT 1
        ");
        $modelCheck->bind_param('ss', $brand, $model_name);
        $modelCheck->execute();
        $modelResult = $modelCheck->get_result();
        $existingModel = $modelResult->fetch_assoc();
        $modelCheck->close();

        if ($existingModel) {
            $model_id = $existingModel['model_id'];
        } else {
            // Create new car model
            $insertModel = $this->db->prepare("
                INSERT INTO tbl_car_model (brand, model_name, type, price)
                VALUES (?, ?, ?, ?)
            ");
            $type = 'Sedan'; // Default type
            $price = 0; // Default price
            $insertModel->bind_param('sssd', $brand, $model_name, $type, $price);
            $insertModel->execute();
            $model_id = $insertModel->insert_id;
            $insertModel->close();
        }

        // Handle image upload
        $image_path = null;
        if (!empty($_FILES['car_image']['name'])) {
            $file = $_FILES['car_image'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file['type'], $allowed)) {
                $_SESSION['auth_error'] = 'Invalid image format. Allowed: PNG, JPG, GIF, WEBP';
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL . '?page=admin-cars-create');
                exit;
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                $_SESSION['auth_error'] = 'Image size exceeds 5MB limit.';
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL . '?page=admin-cars-create');
                exit;
            }

            $uploadDir = APP_ROOT . '/assets/img/cars/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = 'car_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $image_path = 'assets/img/cars/' . $filename;
            }
        }

        // Insert car record
        if ($image_path) {
            $stmt = $this->db->prepare("
                INSERT INTO tbl_car (model_id, plate_number, color, year, transmission, fuel_type, seats, engine, daily_rate, status, image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ississsdsds', $model_id, $plate_number, $color, $year, $transmission, $fuel_type, $seats, $engine, $daily_rate, $status, $image_path);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO tbl_car (model_id, plate_number, color, year, transmission, fuel_type, seats, engine, daily_rate, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ississsds', $model_id, $plate_number, $color, $year, $transmission, $fuel_type, $seats, $engine, $daily_rate, $status);
        }
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = 'Car added successfully!';
        header('Location: ' . BASE_URL . '?page=admin-cars');
        exit;
    }

    // ─────────────────────────────────────────
    //  EDIT CAR FORM
    // ─────────────────────────────────────────
    public function carEdit() {
        $this->requireAdmin();
        
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare("
            SELECT ca.*, cm.model_name, cm.brand
            FROM tbl_car ca
            JOIN tbl_car_model cm ON ca.model_id = cm.model_id
            WHERE ca.car_id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $car = $result->fetch_assoc();
        $stmt->close();

        if (!$car) {
            $_SESSION['error'] = 'Car not found.';
            header('Location: ' . BASE_URL . '?page=admin-cars');
            exit;
        }

        $modelsResult = $this->db->query("
            SELECT * FROM tbl_car_model ORDER BY brand, model_name
        ");
        $carModels = $modelsResult->fetch_all(MYSQLI_ASSOC);

        renderAdmin('admin/cars/edit', compact('car', 'carModels'));
    }

    // ─────────────────────────────────────────
    //  UPDATE CAR
    // ─────────────────────────────────────────
    public function carUpdate() {
        $this->requireAdmin();
        
        $id            = (int)($_POST['car_id'] ?? 0);
        $plate_number  = strtoupper(trim($_POST['plate_number']));
        $color         = trim($_POST['color']);
        $year          = (int)$_POST['year'];
        $status        = $_POST['status'] ?? 'available';
        $transmission  = $_POST['transmission'] ?? 'Automatic';
        $fuel_type     = $_POST['fuel_type'] ?? 'Gasoline';
        $seats         = (int)($_POST['seats'] ?? 5);
        $engine        = trim($_POST['engine'] ?? '');
        $daily_rate    = (float)($_POST['daily_rate'] ?? 0);

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
                plate_number = ?,
                color        = ?,
                year         = ?,
                status       = ?,
                transmission = ?,
                fuel_type    = ?,
                seats        = ?,
                engine       = ?,
                daily_rate   = ?
            WHERE car_id = ?
        ");
        $stmt->bind_param('sssissdsdi', $plate_number, $color, $year, $status, $transmission, $fuel_type, $seats, $engine, $daily_rate, $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = 'Car updated successfully!';
        header('Location: ' . BASE_URL . '?page=admin-cars');
        exit;
    }

    // ─────────────────────────────────────────
    //  DELETE CAR
    // ─────────────────────────────────────────
    public function carDelete() {
        $this->requireAdmin();
        
        $id = (int)($_POST['car_id'] ?? 0);
        
        $check = $this->db->prepare("
            SELECT COUNT(*) as c FROM tbl_booking
            WHERE car_id = ? AND bkng_status IN ('pending','confirmed')
        ");
        $check->bind_param('i', $id);
        $check->execute();
        $result = $check->get_result();
        $row = $result->fetch_assoc();
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

    // ─────────────────────────────────────────
    //  UPDATE CAR STATUS
    // ─────────────────────────────────────────
    public function carStatus() {
        $this->requireAdmin();
        
        $id     = (int)($_POST['car_id'] ?? 0);
        $status = $_POST['status'] ?? 'available';

        $stmt = $this->db->prepare("UPDATE tbl_car SET status = ? WHERE car_id = ?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = 'Car status updated!';
        header('Location: ' . BASE_URL . '?page=admin-cars');
        exit;
    }

    // ─────────────────────────────────────────
    //  BOOKINGS
    // ─────────────────────────────────────────
    public function bookings() {
        $this->requireAdmin();
        
        $result = $this->db->query("
            SELECT b.*, 
                   c.clnt_fname, c.clnt_lname,
                   ca.plate_number,
                   d.drvr_fname, d.drvr_lname
            FROM tbl_booking b
            LEFT JOIN tbl_client c  ON b.client_id = c.client_id
            LEFT JOIN tbl_car ca    ON b.car_id    = ca.car_id
            LEFT JOIN tbl_driver d  ON b.driver_id = d.driver_id
            ORDER BY b.booking_dateAdded DESC
        ");
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
        
        renderAdmin('admin/bookings/index', compact('bookings'));
    }

    // ─────────────────────────────────────────
    //  DRIVERS
    // ─────────────────────────────────────────
    public function drivers() {
        $this->requireAdmin();
        
        $result = $this->db->query("
            SELECT * FROM tbl_driver 
            ORDER BY driver_dateAdded DESC
        ");
        $drivers = $result->fetch_all(MYSQLI_ASSOC);
        
        renderAdmin('admin/drivers/index', compact('drivers'));
    }

    public function clients() {
        $this->requireAdmin();

        $result = $this->db->query("
            SELECT c.*,
                   COUNT(b.booking_id) as total_bookings
            FROM tbl_client c
            LEFT JOIN tbl_booking b ON c.client_id = b.client_id
            WHERE c.role = 'client'
            GROUP BY c.client_id
            ORDER BY c.date_added DESC
        ");
        $clients = $result->fetch_all(MYSQLI_ASSOC);

        renderAdmin('admin/clients/index', compact('clients'));
    }

    // ─────────────────────────────────────────
    //  PAYMENTS (NEW)
    // ─────────────────────────────────────────
    public function payments() {
        $this->requireAdmin();

        $result = $this->db->query("
            SELECT p.*,
                   c.clnt_fname, c.clnt_lname,
                   b.bkng_pickup_date, b.bkng_return_date
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
        
        // Get all messages with proper column aliases
        $result = $this->db->query("
            SELECT 
                contact_id as message_id,
                name as sender_name,
                email,
                subject,
                message,
                is_read,
                contact_dateAdded as created_at
            FROM tbl_contact 
            ORDER BY contact_dateAdded DESC
        ");
        $messages = $result->fetch_all(MYSQLI_ASSOC);

        // Get unread count
        $unreadResult = $this->db->query("SELECT COUNT(*) as count FROM tbl_contact WHERE is_read = 0");
        $unreadRow = $unreadResult->fetch_assoc();
        $unreadCount = $unreadRow['count'] ?? 0;

        // Get specific message if ID is provided
        $currentMessage = null;
        if (!empty($_GET['id'])) {
            $msgId = (int)$_GET['id'];
            $msgResult = $this->db->prepare("
                SELECT 
                    contact_id as message_id,
                    name as sender_name,
                    email,
                    subject,
                    message,
                    is_read,
                    contact_dateAdded as created_at
                FROM tbl_contact 
                WHERE contact_id = ?
            ");
            $msgResult->bind_param('i', $msgId);
            $msgResult->execute();
            $currentMessage = $msgResult->get_result()->fetch_assoc();

            // Mark as read
            if ($currentMessage && !$currentMessage['is_read']) {
                $this->db->query("UPDATE tbl_contact SET is_read = 1 WHERE contact_id = {$msgId}");
            }
        }

        renderAdmin('admin/messages/index', compact('messages', 'unreadCount', 'currentMessage'));
    }

    // ─────────────────────────────────────────
    //  GET MESSAGE (AJAX)
    // ─────────────────────────────────────────
    public function messageRead() {
        $this->requireAdmin();
        
        $msgId = (int)($_GET['id'] ?? 0);
        if (!$msgId) {
            http_response_code(400);
            die(json_encode(['error' => 'Invalid message ID']));
        }

        $msgResult = $this->db->prepare("
            SELECT 
                contact_id as message_id,
                name as sender_name,
                email,
                subject,
                message,
                is_read,
                contact_dateAdded as created_at
            FROM tbl_contact 
            WHERE contact_id = ?
        ");
        $msgResult->bind_param('i', $msgId);
        $msgResult->execute();
        $currentMessage = $msgResult->get_result()->fetch_assoc();

        if (!$currentMessage) {
            http_response_code(404);
            die(json_encode(['error' => 'Message not found']));
        }

        // Mark as read
        if (!$currentMessage['is_read']) {
            $this->db->query("UPDATE tbl_contact SET is_read = 1 WHERE contact_id = {$msgId}");
        }

        header('Content-Type: application/json');
        echo json_encode($currentMessage);
        exit;
    }

    // ─────────────────────────────────────────
    //  DELETE MESSAGE
    // ─────────────────────────────────────────
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
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Message deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete message';
        }

        header('Location: ' . BASE_URL . '?page=admin-messages');
        exit;
    }

    // ─────────────────────────────────────────
    //  REPLY TO MESSAGE (EMAIL)
    // ─────────────────────────────────────────
    public function messageReply() {
        $this->requireAdmin();
        
        $msgId = (int)($_POST['id'] ?? 0);
        $replyMessage = trim($_POST['reply_message'] ?? '');

        if (!$msgId || empty($replyMessage)) {
            $_SESSION['error'] = 'Invalid reply data';
            header('Location: ' . BASE_URL . '?page=admin-messages&id=' . $msgId);
            exit;
        }

        // Get original message
        $msgResult = $this->db->prepare("SELECT email, sender_name, subject FROM tbl_contact WHERE contact_id = ?");
        $msgResult->bind_param('i', $msgId);
        $msgResult->execute();
        $message = $msgResult->get_result()->fetch_assoc();

        if (!$message) {
            $_SESSION['error'] = 'Message not found';
            header('Location: ' . BASE_URL . '?page=admin-messages');
            exit;
        }

        // Send email reply
        require_once APP_ROOT . '/vendor/autoload.php';
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER') ?: 'your-email@gmail.com';
            $mail->Password = getenv('SMTP_PASS') ?: 'your-app-password';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('noreply@cargo.com', 'CARGO Admin');
            $mail->addReplyTo($message['email'], $message['sender_name']);
            $mail->addAddress($message['email']);

            $mail->isHTML(true);
            $mail->Subject = 'Re: ' . $message['subject'];
            $mail->Body = nl2br(htmlspecialchars($replyMessage));
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
    //  WRAPPER METHODS FOR ROUTING
    // ─────────────────────────────────────────
    public function cars() {
        $this->carsIndex();
    }
}