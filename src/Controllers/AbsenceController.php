<?php
namespace App\Controllers;

use App\Models\Absence;
use App\Models\Etudiant;
use App\Models\Settings;                // <--- Nouveau
use App\Services\NotificationService;   // <--- Nouveau
use App\Services\CsvImportService;      // <--- Nouveau

class AbsenceController {

    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    // =========================================================
    // VUE MENSUELLE (Existante)
    // =========================================================
    public function monthlyView() {
        $this->checkAuth();
        $absenceModel = new Absence();
        $etudiantModel = new Etudiant();

        $tables = $absenceModel->getAvailableMonths();

        $selectedMonth = $_GET['month'] ?? ($tables[0]['value'] ?? date('m_Y'));
        
        $monthName = "Mois inconnu";
        foreach ($tables as $t) {
            if ($t['value'] === $selectedMonth) {
                $monthName = $t['label'];
                break;
            }
        }

        $parts = explode('_', $selectedMonth);
        $m = $parts[0] ?? date('m');
        $y = $parts[1] ?? date('Y');

        $absences = $absenceModel->getAllFromMonth($m, $y);

        foreach ($absences as &$abs) {
            $etudiant = $etudiantModel->findByCne($abs['etudiant_cne']);
            if ($etudiant) {
                $abs['nom_complet'] = strtoupper($etudiant['nom']) . ' ' . ucfirst($etudiant['prenom']);
                $abs['classe'] = $etudiant['classe'];
                $abs['email_parent'] = $etudiant['email_parent'];
                // On affiche le tel parent pour info dans la vue
                $abs['tel_parent'] = $etudiant['whatsapp_parent'] ?? $etudiant['telephone_parent'] ?? '-';
            } else {
                $abs['nom_complet'] = 'Inconnu (' . $abs['etudiant_cne'] . ')';
                $abs['classe'] = 'N/A';
                $abs['email_parent'] = '-';
            }
        }

        require_once __DIR__ . '/../Views/absences/monthly.php';
    }
    
    // =========================================================
    // EXPORT CSV (Existant)
    // =========================================================
    public function export() {
        $this->checkAuth();
        
        $month = $_GET['month'] ?? '';
        if (!$month) die("Mois manquant");

        $absenceModel = new Absence();
        list($m, $y) = explode('_', $month);
        
        $data = $absenceModel->getAllFromMonth($m, $y);
        
        $filename = 'Export_Absences_' . $month . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // BOM UTF-8
        
        fputcsv($output, ['Date', 'Heure', 'CNE', 'Matiere', 'Justifie', 'Motif'], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['date_seance'],
                $row['heure_debut'],
                $row['etudiant_cne'],
                $row['matiere'],
                $row['justifie'] ? 'Oui' : 'Non',
                $row['motif']
            ], ';');
        }
        fclose($output);
        exit;
    }

    public function notifyManual() {
        $this->checkAuth();

        // 1. Récupération des paramètres (CNE + MOIS)
        $cne = $_GET['cne'] ?? null;
        $monthStr = $_GET['month'] ?? date('m_Y'); // ex: 01_2026

        if (!$cne) die("CNE manquant.");

        // 2. Initialisation
        $notificationService = new NotificationService();
        $etudiantModel = new Etudiant();
        $absenceModel = new Absence();
        
        $student = $etudiantModel->findByCne($cne);
        if (!$student) die("Étudiant introuvable.");

        // 3. Calcul des données
        // On doit parser le mois (ex: 01_2026 -> 01 et 2026)
        $parts = explode('_', $monthStr);
        $m = $parts[0];
        $y = $parts[1];

        // On compte le total pour ce mois précis
        // (Attention: il faut que ta méthode countByMonth accepte des paramètres optionnels m et y, 
        // sinon elle prend le mois courant. Pour l'instant on suppose mois courant ou que tu adaptes)
        $totalMonth = $absenceModel->countByMonth($cne); 
        $history = $absenceModel->getLastFiveByMonth($cne);

        // 4. Construction HTML (Tableau)
        $htmlList = "<h3>Récapitulatif manuel</h3><ul>";
        foreach ($history as $abs) {
            $htmlList .= "<li>" . date('d/m', strtotime($abs['date_seance'])) . " : " . $abs['motif'] . "</li>";
        }
        $htmlList .= "</ul>";

        // 5. ENVOI EMAIL
        if (!empty($student['email_parent'])) {
            $subject = "Rappel Absences - {$student['nom']} {$student['prenom']}";
            $body = "<p>Bonjour,</p><p>Ceci est un rappel manuel concernant les absences.</p>";
            $body .= "<p>Total du mois : <strong>$totalMonth</strong> absences.</p>";
            $body .= $htmlList;
            
            $notificationService->sendEmail($student['email_parent'], $subject, $body);
        }

        // 6. ENVOI WHATSAPP
        $phoneParent = !empty($student['whatsapp_parent']) 
                       ? $student['whatsapp_parent'] 
                       : ($student['telephone_parent'] ?? null);

        if (!empty($phoneParent)) {
            $waMsg = "👋 *Bonjour (Rappel EIDIA)*\n\n";
            $waMsg .= "Nous attirons votre attention sur le dossier de *{$student['prenom']}*.\n";
            $waMsg .= "Total absences ce mois : *{$totalMonth}*.\n";
            $waMsg .= "Merci de vérifier vos emails pour le détail.";
            
            $notificationService->sendWhatsApp($phoneParent, $waMsg);
        }

        $_SESSION['flash_message'] = "Notifications manuelles envoyées pour {$student['nom']}.";
        
        // Retour à la page précédente
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}