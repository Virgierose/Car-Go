<?php

require_once APP_ROOT . '/app/models/Car.php';

class CarController {

    private Car $car;

    public function __construct() {
        $this->car = new Car();
    }

    public function browse(): void {
        $cars = $this->car->getAll();

        render('cars/browse', [
            'cars' => $cars,
        ]);
    }

    public function details(): void {
        $id  = isset($_GET['id']) ? (int)$_GET['id'] : 1;
        $car = $this->car->getById($id);

        // If car not found, fall back to car #1
        if (!$car) {
            $car = $this->car->getById(1);
        }

        render('cars/details', [
            'car' => $car,
        ]);
    }
}