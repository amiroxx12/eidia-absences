<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Models\User;

class AuthController {
    
    public function login() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/dashboard'); 
            exit;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            
            $authService = new AuthService();
            
            // On vérifie d'abord l'auth (password OK)
            if ($authService->authenticate($email, $password)) {
                
                // On récupère les infos complètes de l'utilisateur pour la session
                $userModel = new User();
                $user = $userModel->findByEmail($email);

                // Si le compte est désactivé
                if ($user['is_active'] == 0) {
                    $error = "Compte désactivé.";
                } else {
                    session_regenerate_id(true); 

                    // On stocke les infos vitales
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nom'];
                    $_SESSION['user_role'] = $user['role']; // <--- IMPORTANT

                    \App\Services\AuditService::log('LOGIN', 'Connexion réussie de ' . $user['email']);

                    header('Location: ' . BASE_URL . '/dashboard');
                    exit;
                }
            } else {
                $error = "Identifiants incorrects.";
            }
        }
        
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // AuthService::logout(); // Si tu as cette méthode, garde-la
        
        // Nettoyage manuel pour être sûr
        $_SESSION = [];
        session_destroy();

        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}