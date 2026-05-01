<?php

class Article extends Model {
    protected $table = 'articles';

    public function getPublished($page = 1, $perPage = 12) {
        $offset = max(0, ($page - 1) * $perPage);
        return $this->db->fetchAll(
            "SELECT *
             FROM {$this->table}
             WHERE status = 'published'
             ORDER BY published_at DESC, created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
    }

    public function countPublished() {
        $row = $this->db->fetchOne("SELECT COUNT(*) AS total FROM {$this->table} WHERE status = 'published'");
        return (int)($row['total'] ?? 0);
    }

    public function findPublishedBySlug($slug) {
        return $this->db->fetchOne(
            "SELECT *
             FROM {$this->table}
             WHERE slug = ? AND status = 'published'
             LIMIT 1",
            [$slug]
        );
    }

    public function getAdminList($page = 1, $perPage = 20) {
        $offset = max(0, ($page - 1) * $perPage);
        return $this->db->fetchAll(
            "SELECT *
             FROM {$this->table}
             ORDER BY created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
    }

    public function countAll() {
        $row = $this->db->fetchOne("SELECT COUNT(*) AS total FROM {$this->table}");
        return (int)($row['total'] ?? 0);
    }

    public function createSlug($title, $ignoreId = null) {
        $base = Helper::slugify($title);
        $slug = $base;
        $index = 1;

        while (true) {
            $params = [$slug];
            $sql = "SELECT id FROM {$this->table} WHERE slug = ?";
            if ($ignoreId !== null) {
                $sql .= " AND id != ?";
                $params[] = $ignoreId;
            }

            $exists = $this->db->fetchOne($sql . " LIMIT 1", $params);
            if (!$exists) {
                return $slug;
            }

            $index++;
            $slug = $base . '-' . $index;
        }
    }
}
