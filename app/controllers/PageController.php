<?php

require_once APP_ROOT . '/app/models/Driver.php';

class PageController {

    public function about(): void {
        if (isset($_SESSION['client_role']) && $_SESSION['client_role'] === 'admin') {
            header('Location: ' . BASE_URL . '?page=admin');
            exit;
        }

        $db = Database::getInstance();

        // Live counts from DB
        $clientCount = (int)($db->query("SELECT COUNT(*) AS t FROM tbl_client")->fetch_assoc()['t'] ?? 0);
        $carCount    = (int)($db->query("SELECT COUNT(*) AS t FROM tbl_car")->fetch_assoc()['t'] ?? 0);

        $drivers = Driver::available();

        render('pages/about', [
            'drivers'     => $drivers,
            'clientCount' => $clientCount,
            'carCount'    => $carCount,
        ]);
    }

    public function contact(): void {
        if (isset($_SESSION['client_role']) && $_SESSION['client_role'] === 'admin') {
            header('Location: ' . BASE_URL . '?page=admin');
            exit;
        }

        $sent = isset($_GET['sent']);

        render('pages/contact', [
            'sent' => $sent,
        ]);
    }

}