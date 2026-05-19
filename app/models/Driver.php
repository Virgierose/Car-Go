<?php

class Driver {

    /**
     * Get all drivers from the database.
     */
    public static function all(): array {
        $db     = Database::getInstance();
        $result = $db->query("
            SELECT driver_id                                 AS id,
                   drvr_fname                                AS fname,
                   drvr_lname                                AS lname,
                   CONCAT(drvr_fname, ' ', drvr_lname)      AS name,
                   drvr_phone_number                         AS phone,
                   rate_per_day                              AS rate,
                   driver_status                             AS status
            FROM tbl_driver
            ORDER BY drvr_fname ASC
        ");

        if (!$result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get only available drivers.
     */
    public static function available(): array {
        $db     = Database::getInstance();
        $stmt   = $db->prepare("
            SELECT driver_id                                 AS id,
                   drvr_fname                                AS fname,
                   drvr_lname                                AS lname,
                   CONCAT(drvr_fname, ' ', drvr_lname)      AS name,
                   drvr_phone_number                         AS phone,
                   rate_per_day                              AS rate,
                   driver_status                             AS status
            FROM tbl_driver
            WHERE driver_status = 'available'
            ORDER BY drvr_fname ASC
        ");

        if (!$stmt) return [];
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Find a single driver by ID.
     */
    public static function find(int $id): ?array {
        $db   = Database::getInstance();
        $stmt = $db->prepare("
            SELECT driver_id                                 AS id,
                   drvr_fname                                AS fname,
                   drvr_lname                                AS lname,
                   CONCAT(drvr_fname, ' ', drvr_lname)      AS name,
                   drvr_phone_number                         AS phone,
                   rate_per_day                              AS rate,
                   driver_status                             AS status
            FROM tbl_driver
            WHERE driver_id = ?
            LIMIT 1
        ");

        if (!$stmt) return null;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }
}