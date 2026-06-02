<?php

class Booking {

    private static function db(): mysqli {
        return Database::getInstance();
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
        $db = self::db();

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

        $types  = '';
        $params = [];

        if ($status && $status !== 'All') {
            $sql   .= " AND b.bkng_status = ?";
            $types .= 's';
            $params[] = $status;
        }

        if ($search) {
            $sql .= " AND (
                c.clnt_fname    LIKE ? OR
                c.clnt_lname    LIKE ? OR
                ca.model_name   LIKE ? OR
                ca.plate_number LIKE ? OR
                d.drvr_fname    LIKE ? OR
                d.drvr_lname    LIKE ?
            )";
            $like = '%' . $search . '%';
            $types .= 'ssssss';
            array_push($params, $like, $like, $like, $like, $like, $like);
        }

        if ($dateFrom) {
            $sql   .= " AND b.pickup_date >= ?";
            $types .= 's';
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $sql   .= " AND b.return_date <= ?";
            $types .= 's';
            $params[] = $dateTo;
        }

        $sql .= " ORDER BY b.booking_id DESC";

        $stmt = $db->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
            WHERE b.client_id = ?
            ORDER BY b.booking_id DESC
        ");
        $stmt->bind_param('i', $clientId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        $result = self::db()->query("
            SELECT
                COUNT(*)                             AS total,
                SUM(bkng_status = 'Active')          AS active,
                SUM(bkng_status = 'Completed')       AS completed,
                SUM(bkng_status = 'Pending')         AS pending,
                COALESCE(SUM(total_price), 0)        AS revenue
            FROM bookings
        ");
        return $result->fetch_assoc();
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
            UPDATE bookings SET bkng_status = ? WHERE booking_id = ?
        ");
        $stmt->bind_param('si', $status, $bookingId);
        return $stmt->execute();
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
            WHERE b.booking_id = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    /**
     * Generate a unique booking reference code.
     */
    public static function generateRef(): string {
        return 'CARGO-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
    }
}