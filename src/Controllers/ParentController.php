<?php

namespace App\Controllers;

use App\Models\ParentToken;
use App\Services\DatabaseService;
use PDO;

class ParentController {
    
    private $db;

    public function __construct() {
        $this->db = DatabaseService::getInstance()->getConnection();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

//ENTREE VIA EMAIL (LIEN MAGIQUE)
    public function verifyLink() {
        $tokenCode = $_GET['token'] ?? null;
        if (!$tokenCode) die("Lien invalide.");

        $tokenModel = new ParentToken();
        $tokenData = $tokenModel->verify($tokenCode);

        if (!$tokenData) die("Ce lien est invalide ou a expiré.");

        $token = $tokenCode; 
        require_once __DIR__ . '/../Views/parent/verify.php';
    }

    
    // 2. VERIFICATION CIN + AIGUILLAGE (NOUVEAU UTILISATEUR VS ANCIEN UTILISATEUR DEJA INSCRIT)
    public function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $cinInput = trim($_POST['cin'] ?? '');
        $tokenCode = $_POST['token'] ?? '';

        // A. Vérif du Token
        $tokenModel = new ParentToken();
        $tokenData = $tokenModel->verify($tokenCode);

        if (!$tokenData) die("Session expirée. Veuillez recliquer sur le lien dans votre email.");

        // B. Vérif du CIN
        $sql = "SELECT * FROM etudiants WHERE id = :id AND cin_parent = :cin";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $tokenData['etudiant_id'], ':cin' => $cinInput]);
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($etudiant) {
            // --- SUCCÈS ---
            $tokenModel->markAsUsed($tokenData['id']);

            // 1. On remplit la session
            $_SESSION['parent_logged_in'] = true;
            $_SESSION['parent_email'] = $tokenData['email_parent']; 
            $_SESSION['parent_name'] = $etudiant['nom_parent'];

            \App\Services\AuditService::log('LOGIN_SUCCESS', "Parent connecté via lien magique : " . $_SESSION['parent_email']);
            // 2. CRUCIAL : On force l'écriture de la session AVANT la redirection
            // Cela empêche le bug où la page suivante ne voit pas la session.
            session_write_close(); 

            // 3. L'Aiguillage
            if ((int)$etudiant['parent_active'] === 0) {
                // Nouveau compte -> Création mot de passe
                header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/set-password');
            } else {
                // Compte existant -> Dashboard direct
                header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/dashboard');
            }
            exit;

        } else {
            // --- ÉCHEC ---
            $error = "Numéro de CIN incorrect pour cet élève.";
            $token = $tokenCode;
            require_once __DIR__ . '/../Views/parent/verify.php';
        }
    }

    
    // 3. REDIRECTION CREATION MOT DE PASSE
    public function showSetPassword() {
        // Sécurité : Si pas de session, retour au login
        if (empty($_SESSION['parent_email'])) {
            header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/login');
            exit;
        }
        require_once __DIR__ . '/../Views/parent/set_password.php';
    }

    // 4. ENREGISTREMENT DU MOT DE PASSE
    public function savePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['parent_email'])) exit;

        $pass = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($pass !== $confirm || strlen($pass) < 6) {
            $error = "Les mots de passe ne correspondent pas ou sont trop courts.";
            require_once __DIR__ . '/../Views/parent/set_password.php';
            return;
        }

        $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

        // Mise à jour de TOUTE la fratrie
        $sql = "UPDATE etudiants SET 
                parent_password = :pass, 
                parent_active = 1, 
                parent_last_login = NOW() 
                WHERE email_parent = :email";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pass' => $hashedPassword,
            ':email' => $_SESSION['parent_email']
        ]);

        $_SESSION['flash_message'] = "Votre compte est activé !";
        header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/dashboard');
        exit;
    }

// 5. LOGIN CLASSIQUE (EMAIL + MDP)
    public function showLogin() {
        require_once __DIR__ . '/../Views/parent/login_form.php';
    }

    public function logout() {
        // On détruit les variables de session spécifiques au parent
        unset($_SESSION['parent_logged_in']);
        unset($_SESSION['parent_email']);
        unset($_SESSION['parent_name']);
        
        // Optionnel : On détruit toute la session pour être sûr
        session_destroy();

        // Redirection vers le LOGIN PARENT (et pas Admin)
        header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/login');
        exit;
    }

    // CORRECTIF BUG 5 : Gestion de l'erreur de login via Session
    public function handleStandardLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $this->db->prepare("SELECT * FROM etudiants WHERE email_parent = :email AND parent_active = 1 LIMIT 1");
        $stmt->execute([':email' => $email]);
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($etudiant && password_verify($password, $etudiant['parent_password'])) {
            $_SESSION['parent_logged_in'] = true;
            $_SESSION['parent_email'] = $etudiant['email_parent'];
            $_SESSION['parent_name'] = $etudiant['nom_parent'];

            \App\Services\AuditService::log('LOGIN_SUCCESS', "Parent connecté (standard) : " . $_SESSION['parent_email']);
            
            $this->db->prepare("UPDATE etudiants SET parent_last_login = NOW() WHERE email_parent = :email")
                     ->execute([':email' => $email]);

            header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/dashboard');
            exit;
        } else {
            // ICI LE CHANGEMENT : On passe par $_SESSION['error_message'] au lieu de $error
            $_SESSION['error_message'] = "Email ou mot de passe incorrect.";
            
            // Et on redirige pour éviter le re-envoi du formulaire
            header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/login');
            exit;
        }
    }


      // 6. DASHBOARD (LOGIQUE FRATRIE)
     
    public function dashboard() {
        if (empty($_SESSION['parent_logged_in'])) {
            header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/login');
            exit;
        }

        $parentEmail = $_SESSION['parent_email']; 
        
        $stmt = $this->db->prepare("SELECT * FROM etudiants WHERE email_parent = :email");
        $stmt->execute([':email' => $parentEmail]);
        $enfants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!$enfants) {
             session_destroy();
             die("Erreur : Aucun élève associé à ce compte parent.");
        }

        $selectedChildId = $_GET['child_id'] ?? $enfants[0]['id'];
        $currentChild = null;
        foreach ($enfants as $e) {
            if ($e['id'] == $selectedChildId) { $currentChild = $e; break; }
        }
        if (!$currentChild) $currentChild = $enfants[0];

        // --- GESTION PAGINATION ---
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5; // 5 absences par page sur mobile

        $absenceModel = new \App\Models\Absence();
        
        // Appel de la méthode mise à jour qui retourne un tableau ['data', 'total']
        $result = $absenceModel->getByEtudiantGlobal($currentChild['cne'], $page, $perPage);
        
        $absences = $result['data'];
        $totalAbsences = $result['total'];
        $totalPages = ceil($totalAbsences / $perPage);
        // --------------------------

        require_once __DIR__ . '/../Views/parent/dashboard.php';
    }

      // 7. GESTION DES JUSTIFICATIFS (UPLOAD)
     
    // 7. GESTION DES JUSTIFICATIFS (UPLOAD SÉCURISÉ)
    public function handleJustification() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
        if (empty($_SESSION['parent_logged_in'])) {
            header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/login');
            exit;
        }

        $absenceId = $_POST['absence_id'] ?? null;
        $tableName = $_POST['table_name'] ?? null;
        $motif = htmlspecialchars($_POST['motif'] ?? '');
        
        // Validation des inputs
        if (!$absenceId || !$tableName) {
            $_SESSION['error_message'] = "Données manquantes.";
            header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/dashboard');
            exit;
        }

        // --- DÉBUT SÉCURITÉ UPLOAD ---
        $fileName = null;

        if (!empty($_FILES['document']['name']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            
            // 1. Limite de taille (ex: 5MB max)
            $maxSize = 5 * 1024 * 1024; 
            if ($_FILES['document']['size'] > $maxSize) {
                $_SESSION['error_message'] = "Le fichier est trop volumineux (Max 5Mo).";
                header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/dashboard');
                exit;
            }

            // 2. Vérification de l'extension
            $fileExtension = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $_SESSION['error_message'] = "Format non supporté. Utilisez JPG, PNG ou PDF.";
                header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/dashboard');
                exit;
            }

            // 3. Vérification du TYPE MIME (Le contenu réel du fichier)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['document']['tmp_name']);
            finfo_close($finfo);

            $allowedMimeTypes = [
                'image/jpeg', 
                'image/png', 
                'application/pdf'
            ];

            if (!in_array($mimeType, $allowedMimeTypes)) {
                $_SESSION['error_message'] = "Le fichier semble corrompu ou invalide.";
                header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/dashboard');
                exit;
            }

            // 4. Préparation du dossier (Permissions 0755 sont plus sûres que 0777)
            $targetDir = __DIR__ . '/../../public/uploads/justifications/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true); 
            }

            // 5. Renommage sécurisé (Évite l'écrasement et les noms bizarres)
            $fileName = 'justif_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
            $targetFile = $targetDir . $fileName;

            if (!move_uploaded_file($_FILES['document']['tmp_name'], $targetFile)) {
                 $_SESSION['error_message'] = "Erreur technique lors de l'enregistrement du fichier.";
                 header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/dashboard');
                 exit;
            }
        }
        // --- FIN SÉCURITÉ UPLOAD ---

        // Sécurité SQL : Nettoyage du nom de la table (C'était déjà bien fait, je garde !)
        $tableName = preg_replace('/[^a-z0-9_]/', '', $tableName);
        
        // Petit fix : Si aucun fichier n'est uploadé, on garde l'ancien ou on met NULL ? 
        // Ici, je pars du principe qu'on met à jour le motif même sans fichier, 
        // mais attention si tu as déjà un fichier, cette requête (telle quelle) pourrait effacer le lien si $fileName est null.
        // Voici une version qui ne touche au fichier que s'il y en a un nouveau :

        $sql = "UPDATE `$tableName` SET 
                justification_status = 'EN_ATTENTE',
                justification_motif = :motif,
                justification_date = NOW()";

        $params = [
            ':motif' => $motif,
            ':id'    => $absenceId
        ];

        if ($fileName) {
            $sql .= ", justification_file = :file";
            $params[':file'] = $fileName;
        }

        $sql .= " WHERE id = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $_SESSION['flash_message'] = "Justification envoyée avec succès.";
        header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/parent/dashboard');
        exit;
    }
}