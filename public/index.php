<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';
require_once APP_ROOT . '/app/helper/MfaHelper.php';

$page = $_GET['page'] ?? 'home';

// Sanitize — allow only letters, numbers, hyphens
if (!preg_match('/^[a-zA-Z0-9\-]+$/', $page)) {
    $page = 'home';
}

$routes = [
    // Public
    'home'                 => ['controller' => 'HomeController',      'method' => 'index'],
    'browse'               => ['controller' => 'CarController',       'method' => 'browse'],
    'car-details'          => ['controller' => 'CarController',       'method' => 'details'],
    'about'                => ['controller' => 'PageController',      'method' => 'about'],
    'contact'              => ['controller' => 'PageController',      'method' => 'contact'],
    // Auth
    'login'                => ['controller' => 'AuthController',      'method' => 'loginForm'],
    'register'             => ['controller' => 'AuthController',      'method' => 'registerForm'],
    'logout'               => ['controller' => 'AuthController',      'method' => 'logout'],
    // MFA
    'mfa-otp'              => ['controller' => 'MfaController',       'method' => 'otpEntry'],
    'mfa-setup'            => ['controller' => 'MfaController',       'method' => 'totpSetup'],
    'mfa-verify'           => ['controller' => 'MfaController',       'method' => 'totpVerify'],
    // Booking
    'price-calculator'     => ['controller' => 'BookingController',   'method' => 'calculator'],
    'driver-selection'     => ['controller' => 'BookingController',   'method' => 'driverSelection'],
    'booking-form'         => ['controller' => 'BookingController',   'method' => 'form'],
    'payment'              => ['controller' => 'BookingController',   'method' => 'payment'],
    'booking-confirmation' => ['controller' => 'BookingController',   'method' => 'confirmation'],
    'confirmation'         => ['controller' => 'BookingController',   'method' => 'confirmation'],
    // Client
    'dashboard'            => ['controller' => 'DashboardController', 'method' => 'index'],
    // Admin
    'admin'                => ['controller' => 'AdminController',     'method' => 'dashboard'],
    'admin-cars'           => ['controller' => 'AdminController',     'method' => 'cars'],
    'admin-cars-create'    => ['controller' => 'AdminController',     'method' => 'carCreate'],
    'admin-cars-store'     => ['controller' => 'AdminController',     'method' => 'carStore'],
    'admin-cars-edit'      => ['controller' => 'AdminController',     'method' => 'carEdit'],
    'admin-cars-update'    => ['controller' => 'AdminController',     'method' => 'carUpdate'],
    'admin-cars-status'    => ['controller' => 'AdminController',     'method' => 'carStatus'],
    'admin-cars-delete'    => ['controller' => 'AdminController',     'method' => 'carDelete'],
    'admin-bookings'       => ['controller' => 'AdminController',     'method' => 'bookings'],
    'admin-bookings-docs'  => ['controller' => 'AdminController',     'method' => 'bookingDocsReview'],
    'admin-drivers'        => ['controller' => 'AdminController',     'method' => 'drivers'],
    'admin-clients'        => ['controller' => 'AdminController',     'method' => 'clients'],
    'admin-payments'       => ['controller' => 'AdminController',     'method' => 'payments'],
    'admin-messages'       => ['controller' => 'AdminController',     'method' => 'messages'],
    'admin-message-read'   => ['controller' => 'AdminController',     'method' => 'messageRead'],
    'admin-message-delete' => ['controller' => 'AdminController',     'method' => 'messageDelete'],
    'admin-message-reply'  => ['controller' => 'AdminController',     'method' => 'messageReply'],
    //driver
    'admin-drivers-create' => ['controller' => 'AdminController', 'method' => 'driverCreate'],
    'admin-drivers-store'  => ['controller' => 'AdminController', 'method' => 'driverStore'],
    'admin-drivers-edit'   => ['controller' => 'AdminController', 'method' => 'driverEdit'],
    'admin-drivers-update' => ['controller' => 'AdminController', 'method' => 'driverUpdate'],
    'admin-drivers-delete' => ['controller' => 'AdminController', 'method' => 'driverDelete'],
];

if (isset($routes[$page])) {
    $controllerName = $routes[$page]['controller'];
    $methodName     = $routes[$page]['method'];
    $controllerFile = APP_ROOT . '/app/controllers/' . $controllerName . '.php';

    if (!file_exists($controllerFile)) {
        http_response_code(500);
        die("Controller not found: <strong>{$controllerName}</strong>");
    }

    require_once $controllerFile;
    $controller = new $controllerName();
    $controller->$methodName();

} else {
    http_response_code(404);
    $view404 = APP_ROOT . '/app/views/pages/404.php';
    if (file_exists($view404)) {
        render('pages/404');
    } else {
        echo '<!DOCTYPE html><html><head><title>404 — CarGo</title>
        <style>body{background:#0a0a0b;color:#f0f0f0;font-family:sans-serif;
        display:flex;align-items:center;justify-content:center;height:100vh;
        margin:0;flex-direction:column;gap:1rem;}a{color:#e05252;}h1{font-size:3rem;}
        </style></head><body><h1>404</h1><p>Page not found.</p>
        <a href="' . BASE_URL . '?page=home">← Back to Home</a></body></html>';
    }
}