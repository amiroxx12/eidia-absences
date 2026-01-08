<?php

namespace App\Services; 

use App\Models\User;

class AuthService {
    
    public function authenticate(string $email, string $password): bool {
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            if (session_status() === PHP_SESSION_NONE) session_start();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nom'] . ' ' . $user['prenom'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user'] = $user; 

            return true;
        }
        return false;
    }

    public static function isLogged() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    // --- CORRECTION CRITIQUE ICI ---
    public static function requireLogin() {
        if(!self::isLogged()) {
            // On utilise la constante BASE_URL sinon ça renvoie sur localhost/login (404)
            header('Location: ' . BASE_URL . '/login'); 
            exit;
        }
    }
    // -------------------------------

    public static function isAdmin() {
        return self::isLogged() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public static function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['user'] ?? null;
    }

    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        if(ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],$params["secure"],$params["httponly"]);
        }
        session_destroy();
    }
}