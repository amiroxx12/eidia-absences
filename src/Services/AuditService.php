<?php
namespace App\Services;

class AuditService {
    
    // Méthode statique pour pouvoir l'appeler partout sans faire "new AuditService()"
    public static function log($action, $details = '') {
        // 1. Récupérer l'utilisateur connecté (si y'en a un)
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = $_SESSION['user_id'] ?? null;

        // 2. Récupérer l'IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        // 3. Insérer en base
        try {
            $db = DatabaseService::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $action, $details, $ip]);
        } catch (\Exception $e) {
            // Si le log plante, on ne veut pas faire planter toute l'appli, donc on ne fait rien (fail silently)
        }
    }
}