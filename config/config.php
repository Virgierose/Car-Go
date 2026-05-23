<?php
// =============================================
// App Configuration
// Path: C:\xampp\htdocs\CarGo\config\config.php
// =============================================

// ── Constants ────────────────────────────────
define('APP_NAME',  'CarGo');
define('APP_ROOT',  dirname(__DIR__));
define('BASE_URL',  '/CarGo/public/index.php');
define('ASSET_URL', '/CarGo/');
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('VIEW_PATH', ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);

// ── Session ───────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Database ──────────────────────────────────
// FIXED: was '/config/Database.php' — now points to the single correct location
require_once APP_ROOT . '/app/core/Database.php';

// ── render() helper ───────────────────────────
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

// ── renderAdmin() helper ───────────────────────
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