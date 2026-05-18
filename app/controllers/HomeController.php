<?php


require_once APP_ROOT . '/app/models/Car.php';

class HomeController {

    public function index(): void {
        // Get featured cars from the model
        $featuredCars = Car::featured();

        // Pass data to the view
        render('home/index', [
            'featuredCars' => $featuredCars,
        ]);
    }
}