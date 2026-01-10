<?php
namespace App\Controllers;

use App\Models\User;

class UserController {
    
    // Middleware de sécurité
    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Si pas connecté OU pas admin -> Dehors
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/dashboard'); // Ou page 403
            exit;
        }
    }

    public function index() {
        $this->checkAdmin(); // Verrouillage
        
        $userModel = new User();
        $users = $userModel->getAllStaff(); // On récupère Admin + Opérateurs
        
        require_once __DIR__ . '/../Views/users/index.php';
    }

    public function create() {
        $this->checkAdmin(); // Verrouillage

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new User();
            
            // Récupération et nettoyage
            $data = [
                'nom' => htmlspecialchars($_POST['nom']),
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'mot_de_passe' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'role' => $_POST['role'], // admin ou operateur
                'is_active' => 1
            ];

            if ($userModel->create($data)) {
                header('Location: ' . BASE_URL . '/users');
                exit;
            } else {
                $error = "Cet email existe déjà.";
                // Idéalement, renvoyer vers la vue avec l'erreur
                require_once __DIR__ . '/../Views/users/index.php'; 
            }
        }
    }

    public function delete() {
        $this->checkAdmin(); // Verrouillage
        
        if (isset($_GET['id'])) {
            $userModel = new User();
            // On empêche de se supprimer soi-même
            if ($_GET['id'] != $_SESSION['user_id']) {
                $userModel->delete($_GET['id']);
            }
        }
        header('Location: ' . BASE_URL . '/users');
        exit;
    }
}