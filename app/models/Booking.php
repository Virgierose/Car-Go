<?php

class Booking {

    private static function db(): PDO {
        return Database::getInstance()->getConnection();
    }

    /**
     * Fetch all bookings with optional filters.
     */
    public static function all(
        ?string $status   = null,
        ?string $search   = null,
        ?string $dateFrom = null,
        ?string $dateTo   = null
    ): array {
        $sql = "
            SELECT
                b.booking_id,
                b.pickup_date,
                b.return_date,
                b.num_days,
                b.total_price,
                b.bkng_status,
                c.clnt_fname,
                c.clnt_lname,
                ca.model_name,
                ca.plate_number,
                d.drvr_fname,
                d.drvr_lname
            FROM bookings b
            JOIN clients c  ON b.client_id = c.client_id
            JOIN cars ca    ON b.car_id    = ca.car_id
            JOIN drivers d  ON b.driver_id = d.driver_id
            WHERE 1=1
        ";

        $params = [];

        if ($status && $status !== 'All') {
            $sql .= " AND b.bkng_status = :status";
            $params[':status'] = $status;
        }

        if ($search) {
            $sql .= " AND (
                c.clnt_fname    LIKE :search OR
                c.clnt_lname    LIKE :search OR
                ca.model_name   LIKE :search OR
                ca.plate_number LIKE :search OR
                d.drvr_fname    LIKE :search OR
                d.drvr_lname    LIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        if ($dateFrom) {
            $sql .= " AND b.pickup_date >= :date_from";
            $params[':date_from'] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= " AND b.return_date <= :date_to";
            $params[':date_to'] = $dateTo;
        }

        $sql .= " ORDER BY b.booking_id DESC";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch bookings for a specific client (user-facing).
     */
    public static function forClient(int $clientId): array {
        $stmt = self::db()->prepare("
            SELECT
                b.booking_id,
                b.pickup_date,
                b.return_date,
                b.num_days,
                b.total_price,
                b.bkng_status,
                ca.model_name,
                ca.plate_number,
                d.drvr_fname,
                d.drvr_lname
            FROM bookings b
            JOIN cars    ca ON b.car_id    = ca.car_id
            JOIN drivers d  ON b.driver_id = d.driver_id
            WHERE b.client_id = :client_id
            ORDER BY b.booking_id DESC
        ");
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch only pending bookings.
     */
    public static function pending(): array {
        return self::all('Pending');
    }

    /**
     * Get aggregate stats for the admin dashboard.
     */
    public static function stats(): array {
        $stmt = self::db()->query("
            SELECT
                COUNT(*)                             AS total,
                SUM(bkng_status = 'Active')          AS active,
                SUM(bkng_status = 'Completed')       AS completed,
                SUM(bkng_status = 'Pending')         AS pending,
                COALESCE(SUM(total_price), 0)        AS revenue
            FROM bookings
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update the status of a booking.
     */
    public static function updateStatus(int $bookingId, string $status): bool {
        $allowed = ['Pending', 'Confirmed', 'Active', 'Completed', 'Cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = self::db()->prepare("
            UPDATE bookings SET bkng_status = :status WHERE booking_id = :id
        ");
        return $stmt->execute([':status' => $status, ':id' => $bookingId]);
    }

    /**
     * Find a single booking by ID.
     */
    public static function find(int $bookingId): ?array {
        $stmt = self::db()->prepare("
            SELECT
                b.booking_id,
                b.pickup_date,
                b.return_date,
                b.num_days,
                b.total_price,
                b.bkng_status,
                c.clnt_fname,
                c.clnt_lname,
                ca.model_name,
                ca.plate_number,
                d.drvr_fname,
                d.drvr_lname
            FROM bookings b
            JOIN clients c  ON b.client_id = c.client_id
            JOIN cars ca    ON b.car_id    = ca.car_id
            JOIN drivers d  ON b.driver_id = d.driver_id
            WHERE b.booking_id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $bookingId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Generate a unique booking reference code.
     */
    public static function generateRef(): string {
        return 'CARGO-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
    }
}