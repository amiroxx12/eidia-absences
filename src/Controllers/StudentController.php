<?php
namespace App\Controllers;

use App\Models\Etudiant;
use App\Models\Absence;

class StudentController {

    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function index() {
        $this->checkAuth(); // Ta méthode de sécu
        
        $etudiantModel = new Etudiant();
        
        // Récupération des filtres depuis l'URL (GET)
        $filters = [
            'search' => $_GET['search'] ?? '',
            'classe' => $_GET['classe'] ?? ''
        ];

        // ON APPELLE LA NOUVELLE MÉTHODE ICI
        $etudiants = $etudiantModel->findAllWithStats($filters);
        
        // On récupère aussi les classes pour le menu déroulant de filtre
        $classes = $etudiantModel->getDistinctClasses();

        require_once __DIR__ . '/../Views/students/index.php';
    }

    public function delete() {
        $this->checkAuth();
        if (isset($_GET['id'])) {
            $model = new Etudiant();
            $model->delete($_GET['id']);
        }
        // On redirige vers la page précédente (ou la liste)
        header('Location: ' . BASE_URL . '/students');
        exit;
    }
    
    public function details() {
        $this->checkAuth();

        if (!isset($_GET['cne'])) {
            header('Location: ' . BASE_URL . '/students');
            exit;
        }

        $cne = $_GET['cne'];

        // 1. Récupérer l'étudiant
        $etudiantModel = new Etudiant();
        $etudiant = $etudiantModel->findByCne($cne);

        if (!$etudiant) {
            die("Étudiant introuvable.");
        }

        // 2. Récupérer ses absences
        // (Assure-toi d'avoir use App\Models\Absence; en haut du fichier)
        $absenceModel = new \App\Models\Absence(); 
        $absences = $absenceModel->getByEtudiant($cne);
        
        // 3. Calculer le total pour l'affichage
        $totalAbsences = count($absences);

        // 4. Charger la vue
        require_once __DIR__ . '/../Views/students/details.php';
    }
    // Note : Pour l'instant, l'Edit se fera plus tard si besoin, 
    // la suppression et la réimportation corrigée suffisent souvent pour commencer.
}