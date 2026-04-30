<?php
// app/Models/DepositRequest.php

class DepositRequest extends Model {
    protected $table = 'deposit_requests';

    public function __construct() {
        parent::__construct();
        $this->ensureTable();
    }

    private function ensureTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS deposit_requests (
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id INT(11) NOT NULL,
                amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                transfer_code VARCHAR(50) NOT NULL,
                bank_code VARCHAR(50) DEFAULT NULL,
                bank_name VARCHAR(255) DEFAULT NULL,
                account_name VARCHAR(255) DEFAULT NULL,
                account_number VARCHAR(50) DEFAULT NULL,
                status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                admin_note TEXT DEFAULT NULL,
                processed_by INT(11) DEFAULT NULL,
                processed_at DATETIME DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY status (status),
                KEY created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function getUserRequests($userId) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
    }

    public function getAllRequests($page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;

        return $this->db->fetchAll(
            "SELECT dr.*, u.name as user_name, u.email as user_email
             FROM {$this->table} dr
             LEFT JOIN users u ON dr.user_id = u.id
             ORDER BY dr.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
    }
}
