<?php

class PageController {

    public function about(): void {
        // Redirect admins away from public pages
        if (isset($_SESSION['client_role']) && $_SESSION['client_role'] === 'admin') {
            header('Location: ' . BASE_URL . '?page=admin');
            exit;
        }

        $team = [
            ['icon'=>'👨‍💼', 'name'=>'Carlos Reyes',    'role'=>'Founder & CEO'],
            ['icon'=>'👩‍💻', 'name'=>'Ana Villanueva',  'role'=>'Operations Manager'],
            ['icon'=>'👨‍🔧', 'name'=>'Mark Aguilar',    'role'=>'Fleet Manager'],
            ['icon'=>'👩‍💼', 'name'=>'Lisa Soriano',    'role'=>'Customer Relations'],
        ];

        render('pages/about', [
            'team' => $team,
        ]);
    }

    public function contact(): void {
        // Redirect admins away from public pages
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