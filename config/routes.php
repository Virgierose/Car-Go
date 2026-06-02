<?php

// ─────────────────────────────────────────────────────────────────────────────
// All Application Routes
// ─────────────────────────────────────────────────────────────────────────────

// ── PUBLIC ────────────────────────────────────────────────────────────────────
$router->get('/',            'HomeController', 'index');
$router->get('/browse-cars', 'CarController',  'index');
$router->get('/car-details', 'CarController',  'show');
$router->get('/about',       'HomeController', 'about');
$router->get('/contact',     'HomeController', 'contact');
$router->post('/contact',    'HomeController', 'sendContact');

// ── AUTH ──────────────────────────────────────────────────────────────────────
$router->get('/login',    'AuthController', 'loginForm');
$router->post('/login',   'AuthController', 'loginForm');
$router->get('/register', 'AuthController', 'registerForm');
$router->post('/register','AuthController', 'register');
$router->get('/logout',   'AuthController', 'logout');

// ── MFA (Two-Factor Authentication) ───────────────────────────────────────────
$router->get('/mfa-otp',     'MfaController', 'otpEntry');
$router->post('/mfa-otp',    'MfaController', 'otpEntry');
$router->get('/mfa-setup',   'MfaController', 'totpSetup');
$router->post('/mfa-verify', 'MfaController', 'totpVerify');

// ── CLIENT DASHBOARD (Role-based redirect from MFA) ──────────────────────────
$router->get('/dashboard',          'ClientController', 'index');
$router->get('/dashboard/bookings', 'ClientController', 'bookings');

// ── BOOKING FLOW ──────────────────────────────────────────────────────────────
$router->get('/booking/calculator',   'BookingController', 'calculator');
$router->get('/booking/driver',       'BookingController', 'driverSelection');
$router->get('/booking/form',         'BookingController', 'form');
$router->post('/booking/form',        'BookingController', 'store');
$router->get('/booking/payment',      'BookingController', 'payment');
$router->post('/booking/payment',     'BookingController', 'processPayment');
$router->get('/booking/confirmation', 'BookingController', 'confirmation');

// ── ADMIN DASHBOARD (Role-based redirect from MFA) ───────────────────────────
$router->get('/admin',          'AdminController', 'index');
$router->get('/admin/cars',     'AdminController', 'cars');
$router->get('/admin/bookings', 'AdminController', 'bookings');
$router->get('/admin/drivers',  'AdminController', 'drivers');

// Admin Car CRUD
$router->get('/admin/cars/create',  'AdminController', 'carCreate');
$router->post('/admin/cars/store',  'AdminController', 'carStore');
$router->get('/admin/cars/edit',    'AdminController', 'carEdit');
$router->post('/admin/cars/update', 'AdminController', 'carUpdate');
$router->post('/admin/cars/status', 'AdminController', 'carStatus');
$router->post('/admin/cars/delete', 'AdminController', 'carDelete');

// Admin Driver CRUD
$router->get('/admin/drivers/create',  'AdminController', 'driverCreate');
$router->post('/admin/drivers/store',  'AdminController', 'driverStore');
$router->get('/admin/drivers/edit',    'AdminController', 'driverEdit');
$router->post('/admin/drivers/update', 'AdminController', 'driverUpdate');
$router->post('/admin/drivers/delete', 'AdminController', 'driverDelete');

// Admin Messages
$router->get('/admin/messages',         'AdminController', 'messages');
$router->get('/admin/messages/read',    'AdminController', 'messageRead');
$router->post('/admin/messages/delete', 'AdminController', 'messageDelete');
$router->post('/admin/messages/reply',  'AdminController', 'messageReply');

//Admin Clients
'admin-clients-status' => ['AdminController', 'clients'],
'admin-bookings-status' => [$adminController, 'bookingStatus'],
// ─────────────────────────────────────────────────────────────────────────────
// NOTE: After successful login + MFA verification, users are redirected based
// on their role via MfaController::finaliseLogin():
//   - Admins (role='admin')  → /admin
//   - Clients (role='client') → /dashboard
// ─────────────────────────────────────────────────────────────────────────────