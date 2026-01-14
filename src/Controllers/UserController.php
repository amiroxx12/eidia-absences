<?php
namespace App\Controllers;

use App\Models\User;
use App\Services\NotificationService;
use App\Services\AuditService;
use App\Models\ParentToken;

class UserController {
    

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Si pas connecté OU pas admin , tu dégages
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { //!isset c'est a dire si on on n'a pas $_SESSION['user_role'] == NULL
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

    // pour créer des comptes aux operateurs ou nouveaux admins

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
                require_once __DIR__ . '/../Views/users/index.php'; 
            }
        }
    }

    // pour effacer un utilisateur

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

    // La page de gestion des parents
    public function parents() {
        $this->checkAdmin(); // y a que l'admin qui peut acceder

        $db = \App\Services\DatabaseService::getInstance()->getConnection();

        $sql = "SELECT 
                    MAX(id) as id, 
                    email_parent, 
                    nom_parent, 
                    MAX(parent_active) as parent_active, 
                    MAX(parent_last_login) as parent_last_login,
                    GROUP_CONCAT(CONCAT(prenom, ' ', nom) SEPARATOR ', ') as enfants
                FROM etudiants 
                WHERE email_parent IS NOT NULL AND email_parent != ''
                GROUP BY email_parent
                ORDER BY parent_active DESC, parent_last_login DESC";

        $accounts = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/users/parents.php';
    }

    // ça envoit le magicLink manuellement
   // DANS src/Controllers/UserController.php

    public function resendMagicLink() {
        $this->checkAdmin(); 

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $parentId = $_POST['parent_id'] ?? null;
            
            if ($parentId) {
                $pdo = \App\Services\DatabaseService::getInstance()->getConnection();
                $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = :id");
                $stmt->execute([':id' => $parentId]);
                $etudiant = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($etudiant && !empty($etudiant['email_parent'])) {
                    
                    // 1. GÉNÉRATION DU TOKEN (LA CLÉ MAGIQUE)
                    $tokenModel = new ParentToken();
                    // On crée un token valide (ex: 7 jours)
                    $token = $tokenModel->create($etudiant['id'], $etudiant['email_parent']);
                    
                    // 2. CRÉATION DU LIEN COMPLET
                    // Le lien ressemblera à : http://localhost/.../parent/verify?token=abc12345
                    $link = BASE_URL . "/parent/verify?token=" . $token; 

                    // 3. ENVOI
                    $notifier = new NotificationService();
                    $to = $etudiant['email_parent'];
                    $subject = "🔐 Accès Espace Parents - EIDIA";
                    
                    $message = "Bonjour,\n\n";
                    $message .= "Voici votre lien magique d'accès direct :\n";
                    $message .= $link . "\n\n";
                    $message .= "Pour créer votre compte, veuillez saisir votre CIN : " . "\n\n";
                    $message .= "Ensuite, créer votre mot de passe. " . "\n\n";
                    $message .= "Cordialement,\nL'Administration.";
                    
                    $result = $notifier->sendEmail($to, $subject, $message);

                    if ($result['success']) {
                        AuditService::log('MAGIC_LINK', "Lien envoyé à {$to} (Parent ID: {$etudiant['id']})");
                        $_SESSION['flash_message'] = "✅ Lien Magique envoyé à " . $to;
                    } else {
                        $_SESSION['error_message'] = "❌ Erreur envoi : " . ($result['message'] ?? 'Erreur inconnue');
                    }
                } else {
                    $_SESSION['error_message'] = "❌ Email introuvable.";
                }
            }
        }
        header('Location: ' . BASE_URL . '/users/parents');
        exit;
    }
}