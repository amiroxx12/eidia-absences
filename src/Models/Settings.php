<?php
namespace App\Models;

use App\Services\DatabaseService;
use PDO;

class Settings {
    private $conn;

    public function __construct() {
        $this->conn = DatabaseService::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM settings");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function update($key, $value) {
        $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v) 
                ON DUPLICATE KEY UPDATE setting_value = :v";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':k' => $key, ':v' => $value]);
    }

    public function getTemplates() {
        return $this->conn->query("SELECT * FROM notification_templates ORDER BY type, channel")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTemplate($id, $subject, $body) {
        $stmt = $this->conn->prepare("UPDATE notification_templates SET subject = :s, body = :b WHERE id = :id");
        return $stmt->execute([':s' => $subject, ':b' => $body, ':id' => $id]);
    }
}