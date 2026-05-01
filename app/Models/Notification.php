<?php
// app/Models/Notification.php

class Notification extends Model {
    protected $table = 'notifications';

    public function send($userId, $title, $message, $type = 'info') {
        return $this->create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type
        ]);
    }

    public function getUnread($userId) {
        return $this->where('user_id', $userId, 'is_read = 0 ORDER BY created_at DESC');
    }

    public function markAsRead($id) {
        return $this->update($id, ['is_read' => 1]);
    }
}
