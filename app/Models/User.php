<?php
// app/Models/User.php

class User extends Model {
    protected $table = 'users';
    
    public function createUser($data) {
        // Generate referral code
        if (!isset($data['referral_code'])) {
            $data['referral_code'] = strtoupper(mb_substr($data['username'], 0, 3, 'UTF-8') . rand(1000, 9999));
        }
        
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $userId = $this->create($data);
        
        // Create wallet for user
        if ($userId) {
            $this->db->insert('wallets', ['user_id' => $userId]);
        }
        
        return $userId;
    }
    
    public function findByEmail($email) {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE email = ?", [$email]);
    }
    
    public function findByUsername($username) {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE username = ?", [$username]);
    }
    
    public function findByGoogleId($googleId) {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE google_id = ?", [$googleId]);
    }
    
    public function getWallet($userId) {
        return $this->db->fetchOne("SELECT * FROM wallets WHERE user_id = ?", [$userId]);
    }
}
