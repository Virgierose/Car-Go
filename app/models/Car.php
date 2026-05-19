<?php

class Car {
    private mysqli $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ── Static entry point (used by HomeController) ───────────────────────────
    public static function featured($limit = 6): array {
        $instance = new self();
        return $instance->getFeatured($limit);
    }

    // ── Static entry point for all cars (used by BookingController) ──────────
    public static function all(): array {
        $instance = new self();
        return $instance->getAll();
    }

    // ── Get featured/available cars ───────────────────────────────────────────
    public function getFeatured($limit = 6): array {
        $sql  = "SELECT c.*, m.model_name, m.brand 
                 FROM tbl_car c
                 LEFT JOIN tbl_car_model m ON c.model_id = m.model_id
                 WHERE c.status = 'available'
                 ORDER BY c.car_dateAdded DESC
                 LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── Get all cars ──────────────────────────────────────────────────────────
    public function getAll(): array {
        $sql    = "SELECT c.*, m.model_name, m.brand 
                   FROM tbl_car c
                   LEFT JOIN tbl_car_model m ON c.model_id = m.model_id
                   ORDER BY c.car_dateAdded DESC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ── Get all car models for dropdown ──────────────────────────────────────
    public function getAllModels(): array {
        $result = $this->db->query("SELECT * FROM tbl_car_model ORDER BY brand ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ── Get single car by ID ──────────────────────────────────────────────────
    public function getById(int $id): array|null {
        $sql  = "SELECT c.*, m.model_name, m.brand 
                 FROM tbl_car c
                 LEFT JOIN tbl_car_model m ON c.model_id = m.model_id
                 WHERE c.car_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ── Create new car ────────────────────────────────────────────────────────
    public function create(array $data): bool {
        $sql  = "INSERT INTO tbl_car (model_id, plate_number, color, year, transmission, fuel_type, seats, engine, daily_rate, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'ississsds',
            $data['model_id'],
            $data['plate_number'],
            $data['color'],
            $data['year'],
            $data['transmission'] ?? 'Automatic',
            $data['fuel_type'] ?? 'Gasoline',
            $data['seats'] ?? 5,
            $data['engine'] ?? '',
            $data['daily_rate'] ?? 0,
            $data['status']
        );
        return $stmt->execute();
    }

    // ── Update car ────────────────────────────────────────────────────────────
    public function update(int $id, array $data): bool {
        $sql  = "UPDATE tbl_car 
                 SET model_id=?, plate_number=?, color=?, year=?, transmission=?, fuel_type=?, seats=?, engine=?, daily_rate=?, status=?
                 WHERE car_id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'ississsdssi',
            $data['model_id'],
            $data['plate_number'],
            $data['color'],
            $data['year'],
            $data['transmission'] ?? 'Automatic',
            $data['fuel_type'] ?? 'Gasoline',
            $data['seats'] ?? 5,
            $data['engine'] ?? '',
            $data['daily_rate'] ?? 0,
            $data['status'],
            $id
        );
        return $stmt->execute();
    }

    // ── Update status only ────────────────────────────────────────────────────
    public function updateStatus(int $id, string $status): bool {
        $allowed = ['available', 'booked', 'maintenance'];
        if (!in_array($status, $allowed)) return false;
        $stmt = $this->db->prepare("UPDATE tbl_car SET status=? WHERE car_id=?");
        $stmt->bind_param('si', $status, $id);
        return $stmt->execute();
    }

    // ── Delete car ────────────────────────────────────────────────────────────
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_car WHERE car_id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // ── Get available cars (for booking flow) ─────────────────────────────────
    public function getAvailable(): array {
        $sql    = "SELECT c.*, m.model_name, m.brand 
                   FROM tbl_car c
                   LEFT JOIN tbl_car_model m ON c.model_id = m.model_id
                   WHERE c.status = 'available'
                   ORDER BY m.brand ASC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}