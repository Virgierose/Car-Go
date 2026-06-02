<?php
// =============================================
// App Configuration
// =============================================
define('APP_NAME',  'CarGo');
define('APP_ROOT',  dirname(__DIR__));
define('BASE_URL',  'https://cargo.bsit2c.site/public/index.php');
define('ASSET_URL', 'https://cargo.bsit2c.site/');
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('VIEW_PATH', ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once APP_ROOT . '/config/Database.php';

if (!function_exists('render')) {
    function render(string $view, array $data = []): void {
        extract($data, EXTR_SKIP);
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(404);
            die('View not found: <strong>' . htmlspecialchars($view) . '</strong>');
        }
        require APP_ROOT . '/app/views/layouts/header.php';
        require $viewFile;
        require APP_ROOT . '/app/views/layouts/footer.php';
    }
}

// Renders a view with NO header/footer wrapper.
// Use for standalone pages like admin-mfa-otp that ship their own full HTML.
if (!function_exists('renderBare')) {
    function renderBare(string $view, array $data = []): void {
        extract($data, EXTR_SKIP);
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(404);
            die('View not found: <strong>' . htmlspecialchars($view) . '</strong>');
        }
        require $viewFile;
    }
}

if (!function_exists('renderAdmin')) {
    function renderAdmin(string $view, array $data = []): void {
        extract($data, EXTR_SKIP);
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(404);
            die('View not found: <strong>' . htmlspecialchars($view) . '</strong>');
        }
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require APP_ROOT . '/app/views/layouts/admin_layout.php';
    }
}