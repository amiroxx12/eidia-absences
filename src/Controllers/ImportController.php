<?php 
namespace App\Controllers;

use App\Services\CsvImportService;
use App\Services\NotificationService;
use App\Models\Etudiant;
use App\Models\Absence;
use App\Models\Settings;

class ImportController {

    // Champs autorisés pour les étudiants (Phase 2)
    private $authorizedColumns = [
        'cne', 'nom', 'prenom', 'email', 'telephone', 'classe',            
        'email_parent', 'telephone_parent', 'whatsapp_parent', 'nom_parent', 'cin_parent', 'adresse'
    ];

    // Champs autorisés pour les absences (Phase 3)
    private $absenceColumns = [
        'etudiant_cne', 'date_seance', 'heure_debut', 'matiere', 'motif'
    ];

    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    private function checkAdmin() {
        $this->checkAuth();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = "Accès refusé. Droits Admin requis.";
            header('Location: ' . BASE_URL . '/dashboard');
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

    // =========================================================================
    // PARTIE 1 : IMPORT ÉTUDIANTS
    // =========================================================================
    
    public function index() {
        $this->checkAdmin();
        require_once __DIR__ . '/../Views/import/upload.php';
    }

    public function upload() {
        $this->checkAdmin();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
             $this->genericUpload(
                 'csv_file', 
                 'csv_import_file', 
                 '/../Views/import/upload.php', 
                 $this->authorizedColumns, 
                 '/../Views/import/mapping.php'
             );
             return;
        }
        require_once __DIR__ . '/../Views/import/upload.php';
    }
    
    // Méthode générique propre pour gérer l'upload et l'analyse
    private function genericUpload($fileInputName, $sessionKey, $uploadView, $columns, $mappingView) {
        $error = null;

        // 1. Vérifier si un fichier a bien été envoyé
        if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
            $error = "Erreur lors du transfert du fichier.";
            require_once __DIR__ . $uploadView;
            return;
        }

        $file = $_FILES[$fileInputName];

        // 2. Vérification de la taille (5 Mo)
        if ($file['size'] > 5 * 1024 * 1024) {
            $error = "Fichier trop volumineux (Max 5 Mo).";
            require_once __DIR__ . $uploadView;
            return;
        }

        // 3. Vérification de l'extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $error = "Format incorrect. Seul le CSV est accepté.";
            require_once __DIR__ . $uploadView;
            return;
        }

        // 4. Déplacement et Analyse
        $uploadDir = __DIR__ . '/../../public/uploads/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $tmpFilePath = $uploadDir . 'temp_' . time() . '_' . bin2hex(random_bytes(4)) . '.csv';
        
        if (move_uploaded_file($file['tmp_name'], $tmpFilePath)) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION[$sessionKey] = $tmpFilePath;

            $importService = new CsvImportService();
            try {
                // Analyse avec le Service mis à jour
                $analysis = $importService->analyzeHeaders($tmpFilePath, $columns);
                
                // Variables pour la vue
                $csvHeaders = $analysis['csv_headers'];
                $suggestedMapping = $analysis['suggested_mapping']; 
                $detectedDelimiter = $analysis['delimiter'];
                $dbColumns = $columns; 
                
                require_once __DIR__ . $mappingView;
                exit;

            } catch (\Exception $e) {
                $error = "Erreur analyse : " . $e->getMessage();
                @unlink($tmpFilePath); 
                require_once __DIR__ . $uploadView;
            }
        } else {
            $error = "Impossible d'enregistrer le fichier.";
            require_once __DIR__ . $uploadView;
        }
    }

    // AJOUT DE LA MÉTHODE PREVIEW (Pour les étudiants)
    public function preview() {
        $this->checkAdmin();
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/import');
            exit;
        }

        $filePath = $_SESSION['csv_import_file'] ?? '';
        $mapping = $_POST['mapping'] ?? [];
        $delimiter = $_POST['delimiter'] ?? ';';

        if (!file_exists($filePath)) die("Fichier expiré ou introuvable.");

        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 0, $delimiter); // Sauter l'en-tête
        
        $previewData = [];
        for ($i = 0; $i < 10; $i++) {
            $row = fgetcsv($handle, 0, $delimiter);
            if ($row) {
                $cleanRow = [];
                foreach ($mapping as $colIndex => $dbField) {
                    if (!empty($dbField) && isset($row[$colIndex])) {
                        $cleanRow[$dbField] = $row[$colIndex]; 
                    }
                }
                if (!empty($cleanRow)) $previewData[] = $cleanRow;
            }
        }
        fclose($handle);
        
        // On passe les variables à la vue preview.php
        require_once __DIR__ . '/../Views/import/preview.php';
    }

    // CORRECTION DE LA MÉTHODE PROCESS (Pour les étudiants)
    public function process() {
        $this->checkAdmin();
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $filePath = $_SESSION['csv_import_file'] ?? '';
        if (!file_exists($filePath)) die("Erreur : Fichier introuvable.");

        // CORRECTION : On décode le JSON si c'est une chaîne, sinon on prend le tableau
        $mapping = $_POST['mapping'] ?? [];
        if (is_string($mapping)) {
            $mapping = json_decode($mapping, true);
        }
        
        $importService = new CsvImportService();
        try {
            $analysis = $importService->analyzeHeaders($filePath, []);
            $delimiter = $analysis['delimiter'];
            
            $model = new Etudiant();
            // Maintenant $mapping est garanti d'être un array
            $stats = $importService->importData($filePath, $mapping, $delimiter, $model);
            
            @unlink($filePath);
            unset($_SESSION['csv_import_file']);
            
            header('Location: ' . BASE_URL . '/students?success=1');
            exit;
        } catch (\Exception $e) {
            die("Erreur lors du traitement final : " . $e->getMessage());
        }
    }

    // =========================================================================
    // PARTIE 2 : IMPORT ABSENCES
    // =========================================================================

    public function uploadAbsences() {
        $this->checkStaff();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_absences'])) {
            $this->genericUpload(
                'csv_absences', 
                'csv_absences_file', 
                '/../Views/import/upload_absences.php', 
                $this->absenceColumns, 
                '/../Views/import/mapping_absences.php'
            );
            return;
        }
        require_once __DIR__ . '/../Views/import/upload_absences.php';
    }

    public function previewAbsences() {
        $this->checkStaff();
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/import/absences');
            exit;
        }

        $filePath = $_SESSION['csv_absences_file'] ?? '';
        $mapping = $_POST['mapping'] ?? [];
        $delimiter = $_POST['delimiter'] ?? ';';

        if (!file_exists($filePath)) die("Fichier expiré.");

        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 0, $delimiter); 
        
        $previewData = [];
        for ($i = 0; $i < 10; $i++) {
            $row = fgetcsv($handle, 0, $delimiter);
            if ($row) {
                $cleanRow = [];
                foreach ($mapping as $colIndex => $dbField) {
                    if (!empty($dbField) && isset($row[$colIndex])) {
                        $cleanRow[$dbField] = $row[$colIndex]; 
                    }
                }
                if (!empty($cleanRow)) $previewData[] = $cleanRow;
            }
        }
        fclose($handle);
        require_once __DIR__ . '/../Views/import/preview_absences.php';
    }

    // =========================================================================
    // PARTIE 2 : IMPORT ABSENCES (AVEC NOTIFICATIONS)
    // =========================================================================

   // =========================================================================
    // PARTIE 2 : IMPORT ABSENCES (Version Finale & Corrigée)
    // =========================================================================

    public function processAbsences() {
        // 1. Sécurité
        $this->checkStaff(); 
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Si on n'est pas en POST, on dégage
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/import/absences');
            exit;
        }

        // 2. Récupération fichier
        $filePath = $_SESSION['csv_absences_file'] ?? '';
        if (!file_exists($filePath)) die("Erreur : Fichier introuvable ou session expirée.");

        // Récupération Mapping
        $mapping = isset($_POST['mapping']) ? (is_array($_POST['mapping']) ? $_POST['mapping'] : json_decode($_POST['mapping'], true)) : [];

        // 3. Initialisation Services
        $importService = new CsvImportService();
        $notificationService = new NotificationService(); 
        $etudiantModel = new Etudiant();
        $absenceModel = new Absence();
        $settingsModel = new Settings();

        $analysis = $importService->analyzeHeaders($filePath, []); 
        $delimiter = $analysis['delimiter'];

        // Chargement Template Email
        $templates = $settingsModel->getTemplates();
        $emailTemplate = null;
        foreach($templates as $t) {
            if($t['type'] === 'absence_nouvelle' && $t['channel'] === 'email') {
                $emailTemplate = $t;
                break;
            }
        }

        $countImported = 0;
        $countNotifs = 0;

        // 4. Traitement
        if (($handle = fopen($filePath, 'r')) !== false) {
            fgetcsv($handle, 0, $delimiter); // Sauter header

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                
                $absenceData = [];
                foreach ($mapping as $csvIndex => $dbColumn) {
                    if (!empty($dbColumn) && isset($row[$csvIndex])) {
                        $absenceData[$dbColumn] = trim($row[$csvIndex]);
                    }
                }

                // --- FIX ANTI-FANTÔME ---
                // On vérifie d'abord si on a un CNE et si l'étudiant existe vraiment
                $cne = $absenceData['etudiant_cne'] ?? null;
                
                if (!$cne || !$etudiantModel->findByCne($cne)) {
                    // Si l'étudiant n'existe pas en base, on ignore cette ligne et on passe à la suivante
                    continue; 
                }
                // ------------------------

                // A. INSERTION
                if ($absenceModel->create($absenceData)) {
                    $countImported++;
                    
                    // B. LOGIQUE DES SEUILS (5, 10, 15...)
                    $totalMonth = $absenceModel->countByMonth($cne);

                    if ($totalMonth > 0 && $totalMonth % 5 == 0) {
                        
                        $student = $etudiantModel->findByCne($cne);

                        if ($student) {
                            
                            // --- PRÉPARATION DONNÉES ---
                            $alertLevel = $totalMonth / 5;
                            $history = $absenceModel->getLastFiveByMonth($cne);
                            
                            // HTML Email
                            $htmlList = "<div style='margin-top:10px; border:1px solid #ddd; border-radius:5px; overflow:hidden;'>";
                            $htmlList .= "<table style='width:100%; border-collapse:collapse; font-size:14px;'>";
                            $htmlList .= "<tr style='background:#f8f9fa; text-align:left;'><th style='padding:8px;'>Date</th><th style='padding:8px;'>Matière</th><th style='padding:8px;'>Motif</th></tr>";
                            
                            foreach ($history as $abs) {
                                $dateStr = date('d/m', strtotime($abs['date_seance'])) . ' à ' . date('H:i', strtotime($abs['heure_debut']));
                                $htmlList .= "<tr>";
                                $htmlList .= "<td style='padding:8px; border-top:1px solid #eee;'>{$dateStr}</td>";
                                $htmlList .= "<td style='padding:8px; border-top:1px solid #eee;'>" . htmlspecialchars($abs['matiere']) . "</td>";
                                $htmlList .= "<td style='padding:8px; border-top:1px solid #eee; color:#dc3545;'>" . htmlspecialchars($abs['motif']) . "</td>";
                                $htmlList .= "</tr>";
                            }
                            $htmlList .= "</table></div>";

                            // --- C. ENVOI EMAIL ---
                            if (!empty($student['email_parent'])) {
                                $vars = [
                                    '{nom_parent}' => $student['nom_parent'] ?? 'Parent',
                                    '{nom_etudiant}' => $student['nom'] . ' ' . $student['prenom'],
                                    '{niveau_alerte}' => $alertLevel,
                                    '{total_absences}' => $totalMonth,
                                    '{liste_absences}' => $htmlList
                                ];

                                if ($emailTemplate) {
                                    $subject = str_replace('{niveau_alerte}', $alertLevel, $emailTemplate['subject'] ?? "Alerte Absences");
                                    $body = $emailTemplate['body'];
                                    foreach ($vars as $key => $val) { $body = str_replace($key, $val, $body); }
                                } else {
                                    $subject = "URGENT : Seuil d'absences dépassé (Niveau $alertLevel)";
                                    $body = "<h1>Avertissement Disciplinaire</h1>";
                                    $body .= "<p>Votre enfant a atteint <strong>$totalMonth absences</strong> ce mois-ci.</p>";
                                    $body .= $htmlList;
                                }

                                $resEmail = $notificationService->sendEmail($student['email_parent'], $subject, $body);
                                if($resEmail['success']) $countNotifs++;
                            }

                            // --- D. ENVOI WHATSAPP (PRIORISÉ) ---
                            // On cherche d'abord dans whatsapp_parent, sinon telephone_parent
                            $phoneParent = !empty($student['whatsapp_parent']) 
                                           ? $student['whatsapp_parent'] 
                                           : ($student['telephone_parent'] ?? null);

                            if (!empty($phoneParent)) {
                                $waMsg = "🚨 *URGENT - EIDIA*\n\n";
                                $waMsg .= "Votre enfant *{$student['prenom']} {$student['nom']}* a atteint *{$totalMonth} absences* ce mois-ci.\n\n";
                                $waMsg .= "⚠️ *C'est INADMISSIBLE.*\n";
                                $waMsg .= "Un rapport disciplinaire détaillé vient d'être envoyé sur votre email.\n\n";
                                $waMsg .= "Merci de consulter votre boîte mail *IMMÉDIATEMENT*.";

                                $notificationService->sendWhatsApp($phoneParent, $waMsg);
                            }
                        }
                    }
                }
            }
            fclose($handle);
        }

        @unlink($filePath);
        unset($_SESSION['csv_absences_file']);

        $msg = "Import terminé : $countImported absences ajoutées.";
        if ($countNotifs > 0) {
            $msg .= " ⚠️ $countNotifs alertes envoyées (Email + WhatsApp).";
        }
        
        $_SESSION['flash_message'] = $msg;
        // Redirection vers la vue mensuelle des absences
        header('Location: ' . BASE_URL . '/absences/monthly'); 
        exit;
    }
}