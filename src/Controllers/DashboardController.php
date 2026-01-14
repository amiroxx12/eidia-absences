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
        
        // 1. Instanciation des modèles
        $etudiantModel = new Etudiant();
        $absenceModel = new Absence();

        // 2. Récupération des données brutes
        $totalEtudiants = $etudiantModel->countAll();
        $totalAbsences = $absenceModel->countAllGlobal(); // Nombre de créneaux ratés

        // --- 3. ALGORITHME D'ESTIMATION DU TAUX DE PRÉSENCE (Solution 2) ---
        
        // A. Configuration (Tu peux ajuster ces chiffres selon ton école)
        $dateRentree = '2025-09-15'; // Date du début des cours
        $heuresParSemaine = 28;      // Volume horaire moyen hebdomadaire
        $dureeCreneau = 1.5;         // Durée moyenne d'une séance (1h30)

        // B. Calcul du temps écoulé
        $debut = new \DateTime($dateRentree);
        $fin = new \DateTime(); // Date d'aujourd'hui
        
        // Si on est avant la rentrée, tout le monde est présent à 100%
        if ($fin < $debut) {
            $tauxPresence = 100;
        } else {
            // Nombre de jours écoulés
            $diff = $debut->diff($fin);
            $joursEcoules = $diff->days;
            
            // Conversion en semaines de cours (Semaines réelles - Vacances estimées)
            $semainesBrutes = floor($joursEcoules / 7);
            
            // Petite astuce : on enlève ~2 semaines de vacances toutes les 15 semaines
            $semainesReelles = $semainesBrutes - (floor($semainesBrutes / 15) * 2);
            if ($semainesReelles < 1) $semainesReelles = 1; // Sécurité

            // C. Calcul du Volume Horaire Théorique GLOBAL
            // (Si personne n'avait jamais raté un cours)
            $totalHeuresTheoriques = $totalEtudiants * $semainesReelles * $heuresParSemaine;
            
            // D. Calcul du Volume d'Heures Ratées
            $totalHeuresRatees = $totalAbsences * $dureeCreneau;

            // E. Calcul du Pourcentage
            if ($totalHeuresTheoriques > 0) {
                $tauxPresence = 100 - (($totalHeuresRatees / $totalHeuresTheoriques) * 100);
            } else {
                $tauxPresence = 100;
            }
        }

        // 4. Envoi des données à la vue
        $stats = [
            'total_etudiants' => $totalEtudiants,
            'total_classes'   => $etudiantModel->countClasses(),
            'total_absences'  => $totalAbsences,
            // On limite le chiffre entre 0 et 100 et on arrondit à 1 chiffre après la virgule
            'taux_presence'   => max(0, min(100, round($tauxPresence, 1))) 
        ];

        require_once __DIR__ . '/../Views/dashboard.php';
    }
}