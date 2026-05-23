<?php

// Database is already loaded by config.php — no require needed here

class Driver {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ─────────────────────────────────────────
    //  Static — used by BookingController
    // ─────────────────────────────────────────

    public static function available(): array {
        $db   = Database::getInstance();
        $stmt = $db->prepare("
            SELECT
                driver_id                                AS id,
                CONCAT(drvr_fname, ' ', drvr_lname)     AS name,
                rate_per_day                             AS fee,
                driver_status                            AS status
            FROM tbl_driver
            WHERE driver_status = 'available'
            ORDER BY drvr_lname ASC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ─────────────────────────────────────────
    //  Instance — used by AdminController
    // ─────────────────────────────────────────

    public function getAll(string $search = '', string $status = ''): array {
        $sql    = "SELECT * FROM tbl_driver WHERE 1=1";
        $params = [];
        $types  = '';

        if ($search !== '') {
            $like    = '%' . $search . '%';
            $sql    .= " AND (drvr_fname LIKE ? OR drvr_lname LIKE ? OR drvr_license_no LIKE ?)";
            $params  = array_merge($params, [$like, $like, $like]);
            $types  .= 'sss';
        }

        if ($status !== '') {
            $sql    .= " AND driver_status = ?";
            $params[] = $status;
            $types   .= 's';
        }

        $sql .= " ORDER BY drvr_lname ASC";

        if (empty($params)) {
            $result = $this->db->query($sql);
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM tbl_driver WHERE driver_id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function licenseExists(string $license, int $excludeId = 0): bool {
        $stmt = $this->db->prepare(
            "SELECT driver_id FROM tbl_driver
             WHERE drvr_license_no = ? AND driver_id != ? LIMIT 1"
        );
        $stmt->bind_param('si', $license, $excludeId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO tbl_driver
                (drvr_fname, drvr_mname, drvr_lname,
                 drvr_phone_number, drvr_license_no,
                 rate_per_day, driver_status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $fname   = $data['drvr_fname'];
        $mname   = $data['drvr_mname']    ?? '';
        $lname   = $data['drvr_lname'];
        $phone   = $data['drvr_phone_number'];
        $license = $data['drvr_license_no'];
        $rate    = (float)($data['rate_per_day'] ?? 0);
        $status  = $data['driver_status']        ?? 'available';

        $stmt->bind_param('sssssds', $fname, $mname, $lname, $phone, $license, $rate, $status);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE tbl_driver SET
                drvr_fname        = ?,
                drvr_mname        = ?,
                drvr_lname        = ?,
                drvr_phone_number = ?,
                drvr_license_no   = ?,
                rate_per_day      = ?,
                driver_status     = ?
            WHERE driver_id = ?
        ");

        $fname   = $data['drvr_fname'];
        $mname   = $data['drvr_mname']    ?? '';
        $lname   = $data['drvr_lname'];
        $phone   = $data['drvr_phone_number'];
        $license = $data['drvr_license_no'];
        $rate    = (float)($data['rate_per_day'] ?? 0);
        $status  = $data['driver_status']        ?? 'available';

        $stmt->bind_param('sssssdsi', $fname, $mname, $lname, $phone, $license, $rate, $status, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_driver WHERE driver_id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}