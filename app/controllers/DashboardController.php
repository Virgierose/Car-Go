<?php

require_once APP_ROOT . '/app/models/Booking.php';

class DashboardController {
    // ── GUARD ────────────────────────────────────────────────────────────────
    private function requireClient(): void {
        if (empty($_SESSION['client_id'])) {
            header('Location: ' . BASE_URL . '?page=login');
            exit;
        }
    }

    public function index(): void {
        $this->requireClient();
        
        $bookings = Booking::forClient();
        $stats    = Booking::stats();

        render('dashboard/index', [
            'bookings' => $bookings,
            'stats'    => $stats,
        ]);
    }
}