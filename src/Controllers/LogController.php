<?php
namespace App\Controllers;

use App\Services\DatabaseService;

class LogController {
    
    public function index() {
        // 1. Sécurité
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/dashboard'); 
            exit;
        }

        // 2. Récupération des données
        $db = DatabaseService::getInstance()->getConnection();
        
        // On récupère les 100 derniers logs avec le nom de l'utilisateur
        // On utilise 'utilisateurs' ou 'users' selon le nom réel de ta table (vérifie ça !)
        // Dans ton User.php précédent c'était 'utilisateurs', donc je garde ça.
        $sql = "SELECT l.*, u.nom as user_nom, u.email as user_email
                FROM logs l 
                LEFT JOIN utilisateurs u ON l.user_id = u.id 
                ORDER BY l.created_at DESC LIMIT 100";
                
        $logs = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Appel de la Vue
        require_once __DIR__ . '/../Views/admin/logs/index.php';
    }
}