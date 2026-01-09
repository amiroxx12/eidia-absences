<?php
namespace App\Controllers;

use App\Models\Etudiant;
use App\Models\Absence;

class DashboardController {

    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function index() {
        $this->checkAuth();
        
        // 1. Instancier les modèles
        $etudiantModel = new Etudiant();
        $absenceModel = new Absence();

        // 2. Récupérer les vrais chiffres et les mettre dans le tableau $stats
        // C'est ce tableau que la vue dashboard.php utilise ($stats['total_etudiants']...)
        $stats = [
            'total_etudiants' => $etudiantModel->countAll(),
            'total_classes'   => $etudiantModel->countClasses(),
            'total_absences'  => $absenceModel->countAllGlobal()
        ];

        // 3. Passer les données à la vue
        require_once __DIR__ . '/../Views/dashboard.php';
    }
}