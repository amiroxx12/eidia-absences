<?php
namespace App\Controllers;
use App\Services\AuthService;

class AuthController {
    
    public function login() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // CORRECTION REDIRECTION
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/import'); 
            exit;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            
            $authService = new AuthService();
            if ($authService->authenticate($email, $password)) {
                // CORRECTION REDIRECTION
                header('Location: ' . BASE_URL . '/import');
                exit;
            } else {
                $error = "Identifiants incorrects.";
            }
        }
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function logout() {
        AuthService::logout(); // Utilise ta méthode statique
        // CORRECTION REDIRECTION
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}