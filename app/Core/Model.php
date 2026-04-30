<?php
// app/Core/Model.php

class Model {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function all($orderBy = 'id DESC', $limit = null) {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        return $this->db->fetchAll($sql);
    }
    
    public function find($id) {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }
    
    public function where($column, $value) {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE {$column} = ?", [$value]);
    }
    
    public function create($data) {
        return $this->db->insert($this->table, $data);
    }
    
    public function update($id, $data) {
        return $this->db->update($this->table, $data, 'id = :id', ['id' => $id]);
    }
    
    public function delete($id) {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }
    
    public function count($where = '1=1', $params = []) {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}", $params);
        return $result['total'] ?? 0;
    }
}
