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

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // On vérifie si l'utilisateur est connecté ET s'il est admin
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
        // N'oublie pas d'instancier le modèle Absence pour faire les calculs
        $absenceModel = new \App\Models\Absence(); 
        
        // Récupération des filtres
        $filters = [
            'search' => $_GET['search'] ?? '',
            'classe' => $_GET['classe'] ?? ''
        ];

        // 1. On récupère les étudiants (Liste brute)
        $etudiants = $etudiantModel->findAllWithStats($filters);
        
        // 2. On injecte le nombre d'absences pour chaque étudiant (Calcul dynamique)
        // On passe par référence (&$etudiant) pour modifier le tableau directement
        foreach ($etudiants as &$etudiant) {
            $etudiant['total_absences'] = $absenceModel->countTotalByStudent($etudiant['cne']);
        }
        unset($etudiant); // Sécurité après une boucle par référence

        // 3. (Optionnel) On refait le tri pour mettre les plus absents en haut
        // usort($etudiants, function($a, $b) {
        //     return $b['total_absences'] - $a['total_absences'];
        // });
        
        // Récupération des classes pour le filtre
        $classes = $etudiantModel->getDistinctClasses();

        require_once __DIR__ . '/../Views/students/index.php';
    }
    
    public function details() {
        $this->checkAuth();

        // 1. On vérifie qu'on a bien reçu le CNE dans l'URL
        if (!isset($_GET['cne'])) {
            header('Location: ' . BASE_URL . '/students');
            exit;
        }

        // C'est cette ligne qui manquait !
        $cne = $_GET['cne'];

        // 2. Récupérer l'étudiant
        $etudiantModel = new Etudiant();
        $etudiant = $etudiantModel->findByCne($cne);

        if (!$etudiant) {
            die("Étudiant introuvable (CNE: " . htmlspecialchars($cne) . ")");
        }

        // 3. Récupérer ses absences (Mode Multi-Tables : Janvier, Février...)
        $absenceModel = new \App\Models\Absence(); 
        
        // On utilise la méthode qui scanne toutes les tables mensuelles
        $absences = $absenceModel->getByEtudiantGlobal($cne);
        
        // Calcul du total pour l'affichage
        $totalAbsences = count($absences);

        // 4. Charger la vue
        require_once __DIR__ . '/../Views/students/details.php';
    }
    public function delete() { // <--- PARENTHÈSES VIDES !
        $this->checkAdmin(); 

        // 1. On récupère le CNE depuis l'URL (?cne=...)
        $cne = $_GET['cne'] ?? null;

        if ($cne) {
            $model = new Etudiant();
            
            // 2. On appelle la méthode du Modèle (C'est elle qui prend le CNE en argument)
            if ($model->delete($cne)) {
                $_SESSION['flash_message'] = "L'étudiant $cne a été supprimé (Nettoyage complet).";
            } else {
                $_SESSION['error_message'] = "Erreur lors de la suppression.";
            }
        } else {
            $_SESSION['error_message'] = "CNE manquant.";
        }

        // 3. Retour à la liste
        header('Location: ' . BASE_URL . '/students');
        exit;
    }
    // Note : Pour l'instant, l'Edit se fera plus tard si besoin, 
    // la suppression et la réimportation corrigée suffisent souvent pour commencer.
}