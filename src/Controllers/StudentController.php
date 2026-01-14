<?php
namespace App\Controllers;

use App\Models\Etudiant;
use App\Models\Absence;

class StudentController {

    // 1. DÉCLARATION DE LA PROPRIÉTÉ 
    private $studentModel;

    // 2. CONSTRUCTEUR (Initialisation) 
    public function __construct() {
        // On charge le Modèle Etudiant dès que le contrôleur est appelé
        $this->studentModel = new Etudiant();
    }

    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = "Accès refusé. Action réservée aux administrateurs.";
            header('Location: ' . BASE_URL . '/students');
            exit;
        }
    }

    private function checkStaff() {
        $this->checkAuth();
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'operateur'])) {
            $_SESSION['error_message'] = "Accès refusé. Réservé au personnel.";
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    public function index() {
        $this->checkAuth();
        
        $etudiantModel = new Etudiant();
        $absenceModel = new \App\Models\Absence(); 
        
        $filters = [
            'search' => $_GET['search'] ?? '',
            'classe' => $_GET['classe'] ?? ''
        ];

        // 1. Liste brute
        $etudiants = $etudiantModel->findAllWithStats($filters);
        
        // 2. Injection du nombre d'absences
        foreach ($etudiants as &$etudiant) {
            $etudiant['total_absences'] = $absenceModel->countTotalByStudent($etudiant['cne']);
        }
        unset($etudiant); 

        // Récupération des classes
        $classes = $etudiantModel->getDistinctClasses();

        require_once __DIR__ . '/../Views/students/index.php';
    }
    
    public function details() {
        $this->checkAuth();

        if (!isset($_GET['cne'])) {
            header('Location: ' . BASE_URL . '/students');
            exit;
        }

        $cne = $_GET['cne'];

        $etudiantModel = new Etudiant();
        $etudiant = $etudiantModel->findByCne($cne);

        if (!$etudiant) {
            die("Étudiant introuvable (CNE: " . htmlspecialchars($cne) . ")");
        }

        $absenceModel = new \App\Models\Absence(); 
        $absences = $absenceModel->getByEtudiantGlobal($cne);
        $totalAbsences = count($absences);

        require_once __DIR__ . '/../Views/students/details.php';
    }

    public function delete() {
        // 1. Démarrage Session
        if (session_status() === PHP_SESSION_NONE) session_start();

        // On vérifie 'user_id' (Admin)
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // 2. Traitement du POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if ($id) {
                // A. On récupère le CNE via l'ID
                // MAINTENANT ÇA MARCHE car $this->studentModel existe grâce au constructeur !
                $studentData = $this->studentModel->find($id);
                
                if ($studentData && !empty($studentData['cne'])) {
                    $cne = $studentData['cne'];

                    // B. Suppression Nucléaire
                    if ($this->studentModel->delete($cne)) {
                        $_SESSION['flash_message'] = "✅ L'étudiant (CNE: $cne) et tout son historique d'absences ont été supprimés.";
                    } else {
                        $_SESSION['error_message'] = "❌ Erreur SQL lors de la suppression.";
                    }
                } else {
                    $_SESSION['error_message'] = "❌ Étudiant introuvable ou CNE manquant.";
                }
            } else {
                 $_SESSION['error_message'] = "❌ Aucun ID reçu.";
            }
        }
        
        // 3. Retour à la liste
        header('Location: ' . BASE_URL . '/students');
        exit;
    }
}