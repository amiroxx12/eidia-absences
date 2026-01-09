<?php
namespace App\Controllers;

use App\Services\AuthService;

class AuthController {
    
    public function login() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // 1. Si déjà connecté, on envoie au Dashboard (pas Import)
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/dashboard'); 
            exit;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Nettoyage des entrées
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            
            $authService = new AuthService();
            
            // On suppose que ta méthode authenticate() vérifie le pass ET remplit $_SESSION['user_id']
            if ($authService->authenticate($email, $password)) {
                
                // 🛡️ SÉCURITÉ CRITIQUE : Protection contre le vol de session
                // On génère un nouvel ID de session tout en gardant les infos (user_id)
                session_regenerate_id(true); 

                // 2. Redirection vers le Tableau de bord
                header('Location: ' . BASE_URL . '/dashboard');
                exit;
            } else {
                $error = "Identifiants incorrects.";
            }
        }
        
        // Affichage de la vue
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function logout() {
        // On détruit la session proprement
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Si tu as une méthode statique dans AuthService, c'est bien, 
        // sinon on peut le faire manuellement ici :
        // $_SESSION = []; session_destroy();
        AuthService::logout(); 

        // Redirection vers le login
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}