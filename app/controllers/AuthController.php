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

if (!class_exists('AuthController')) :
class AuthController {

    // ── LOGIN FORM (GET) & LOGIN SUBMIT (POST) ──────────────────────────────
    public function loginForm(): void {
        // If already logged in, redirect to dashboard/admin
        if (isset($_SESSION['client_id'])) {
            $role = $_SESSION['client_role'] ?? 'client';
            header('Location: ' . BASE_URL . '?page=' . ($role === 'admin' ? 'admin' : 'dashboard'));
            exit;
        }

        // GET: Show login form
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $error   = $_SESSION['auth_error']   ?? null;
            $success = $_SESSION['auth_success'] ?? null;
            $errors  = $_SESSION['auth_errors']  ?? [];
            unset($_SESSION['auth_error'], $_SESSION['auth_success'], $_SESSION['auth_errors']);

            render('auth/login', compact('error', 'success', 'errors'));
            return;
        }

        // POST: Process login form
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $errors   = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        }

        if (!empty($errors)) {
            $_SESSION['auth_errors'] = $errors;
            header('Location: ' . BASE_URL . '?page=login');
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT client_id, clnt_fname, clnt_lname, email, password, role
             FROM tbl_client WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $client = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$client || !password_verify($password, $client['password'])) {
            $_SESSION['auth_error'] = 'Invalid email or password. Please try again.';
            header('Location: ' . BASE_URL . '?page=login');
            exit;
        }

        // Password OK → store pending session and go to MFA
        session_regenerate_id(true);
        $_SESSION['mfa_pending_client_id'] = $client['client_id'];
        $_SESSION['mfa_pending_role']      = $client['role'];

        header('Location: ' . BASE_URL . '?page=mfa-otp');
        exit;
    }

    // ── REGISTER FORM (GET) & REGISTER SUBMIT (POST) ───────────────────────
    public function registerForm(): void {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['client_id'])) {
            header('Location: ' . BASE_URL . '?page=dashboard');
            exit;
        }

        // GET: Show register form
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $error   = $_SESSION['auth_error']   ?? null;
            $success = $_SESSION['auth_success'] ?? null;
            $errors  = $_SESSION['auth_errors']  ?? [];
            unset($_SESSION['auth_error'], $_SESSION['auth_success'], $_SESSION['auth_errors']);

            render('auth/register', compact('error', 'success', 'errors'));
            return;
        }

        // POST: Process registration form
        $fname    = trim($_POST['clnt_fname']        ?? '');
        $lname    = trim($_POST['clnt_lname']        ?? '');
        $mname    = trim($_POST['clnt_mname']        ?? '');
        $email    = trim($_POST['email']             ?? '');
        $phone    = trim($_POST['clnt_phone_number'] ?? '');
        $address  = trim($_POST['adress']            ?? '');
        $password = trim($_POST['password']          ?? '');
        $confirm  = trim($_POST['confirm_password']  ?? '');
        $errors   = [];

        if (empty($fname))   $errors['clnt_fname'] = 'First name is required.';
        if (empty($lname))   $errors['clnt_lname'] = 'Last name is required.';
        if (empty($address)) $errors['adress']     = 'Address is required.';

        if (empty($phone)) {
            $errors['clnt_phone_number'] = 'Phone number is required.';
        } elseif (!preg_match('/^(\+?63|0)9\d{9}$/', preg_replace('/\s+/', '', $phone))) {
            $errors['clnt_phone_number'] = 'Enter a valid PH number (e.g. 09XX XXX XXXX).';
        }

        if (empty($email)) {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            $_SESSION['auth_errors'] = $errors;
            header('Location: ' . BASE_URL . '?page=register');
            exit;
        }

        $db = Database::getInstance();

        $check = $db->prepare("SELECT client_id FROM tbl_client WHERE email = ? LIMIT 1");
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();
        $exists = $check->num_rows > 0;
        $check->close();

        if ($exists) {
            $_SESSION['auth_error'] = 'An account with this email already exists.';
            header('Location: ' . BASE_URL . '?page=register');
            exit;
        }

        $hashed    = password_hash($password, PASSWORD_BCRYPT);
        $dateAdded = date('Y-m-d H:i:s');

        $stmt = $db->prepare(
            "INSERT INTO tbl_client
             (clnt_fname, clnt_lname, clnt_mname, email, clnt_phone_number, adress, password, client_dateAdded)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssssss', $fname, $lname, $mname, $email, $phone, $address, $hashed, $dateAdded);

        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['auth_success'] = 'Account created! You can now sign in.';
            header('Location: ' . BASE_URL . '?page=login');
        } else {
            $stmt->close();
            $_SESSION['auth_error'] = 'Something went wrong. Please try again.';
            header('Location: ' . BASE_URL . '?page=register');
        }
        exit;
    }

    // ── LOGOUT ───────────────────────────────────────────────────────────────
    public function logout(): void {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '?page=login');
        exit;
    }
}
endif;