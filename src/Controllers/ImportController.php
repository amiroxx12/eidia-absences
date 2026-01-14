<?php 
namespace App\Controllers;

use App\Services\CsvImportService;
use App\Services\NotificationService; // Indispensable pour l'envoi
use App\Services\AuditService;
use App\Models\Etudiant;
use App\Models\Absence;
use App\Models\Settings;

class ImportController {

    private $authorizedColumns = [
        'cne', 'nom', 'prenom', 'email', 'telephone', 'classe',            
        'email_parent', 'telephone_parent', 'whatsapp_parent', 'nom_parent', 'cin_parent', 'adresse'
    ];

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
            $_SESSION['error_message'] = "Accès refusé.";
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    private function checkStaff() {
        $this->checkAuth();
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'operateur'])) {
            $_SESSION['error_message'] = "Accès refusé.";
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    // --- PARTIE ETUDIANTS (INCHANGÉE) ---
    public function index() {
        $this->checkAdmin();
        require_once __DIR__ . '/../Views/import/upload.php';
    }

    public function upload() {
        $this->checkAdmin();
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
             $this->genericUpload('csv_file', 'csv_import_file', '/../Views/import/upload.php', $this->authorizedColumns, '/../Views/import/mapping.php');
             return;
        }
        require_once __DIR__ . '/../Views/import/upload.php';
    }
    
    private function genericUpload($fileInputName, $sessionKey, $uploadView, $columns, $mappingView) {
        $error = null;

        // 1. Vérification basique (Upload OK ?)
        if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
            $error = "Erreur lors du transfert du fichier.";
            require_once __DIR__ . $uploadView;
            return;
        }

        $file = $_FILES[$fileInputName];

        // 2. Vérification de la taille (Mise à jour pour 10 Mo comme demandé)
        // 10 Mo = 10 * 1024 * 1024
        if ($file['size'] > 10 * 1024 * 1024) {
            $error = "Fichier trop volumineux (Max 10 Mo).";
            require_once __DIR__ . $uploadView;
            return;
        }

        // 3. Vérification de l'extension (Sécurité niveau 1)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $error = "Format incorrect. L'extension doit être strictement .csv";
            require_once __DIR__ . $uploadView;
            return;
        }

        // 4. --- SÉCURITÉ MIME TYPE (Sécurité niveau 2 - Binaire) ---
        // On demande à PHP quel est le VRAI type du fichier, pas juste son nom
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // Liste des types MIME valides pour un CSV (couvre Windows, Mac, Linux)
        $allowedMimes = [
            'text/plain', 
            'text/csv', 
            'text/x-csv', 
            'application/vnd.ms-excel', 
            'application/csv', 
            'application/x-csv', 
            'text/x-comma-separated-values', 
            'text/comma-separated-values'
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            $error = "Fichier corrompu ou dangereux. Type détecté : " . htmlspecialchars($mimeType);
            require_once __DIR__ . $uploadView;
            return;
        }

        // 5. --- SCAN ANTI-INJECTION (Sécurité niveau 3 - Contenu) ---
        // On lit les premiers octets pour vérifier s'il n'y a pas de code PHP caché
        $content = file_get_contents($file['tmp_name'], false, null, 0, 2048); 
        if (strpos($content, '<?php') !== false || strpos($content, '<?') !== false) {
            $error = "⚠️ ALERTE SÉCURITÉ : Code suspect détecté dans le fichier.";
            // (Optionnel) Ici tu pourrais logguer l'attaque dans ton futur AuditService
            require_once __DIR__ . $uploadView;
            return;
        }

        // 6. Traitement standard (si tout est vert)
        $uploadDir = __DIR__ . '/../../public/uploads/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        // Nom aléatoire pour éviter l'écrasement
        $tmpFilePath = $uploadDir . 'safe_import_' . time() . '_' . bin2hex(random_bytes(4)) . '.csv';
        
        if (move_uploaded_file($file['tmp_name'], $tmpFilePath)) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION[$sessionKey] = $tmpFilePath;

            $importService = new \App\Services\CsvImportService();
            try {
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
            $error = "Erreur serveur lors de l'enregistrement.";
            require_once __DIR__ . $uploadView;
        }
    }

    public function preview() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/import'); exit; }

        $filePath = $_SESSION['csv_import_file'] ?? '';
        $mapping = $_POST['mapping'] ?? [];
        $delimiter = $_POST['delimiter'] ?? ';';

        if (!file_exists($filePath)) die("Fichier introuvable.");

        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 0, $delimiter); 
        $previewData = [];
        for ($i = 0; $i < 10; $i++) {
            $row = fgetcsv($handle, 0, $delimiter);
            if ($row) {
                $cleanRow = [];
                foreach ($mapping as $colIndex => $dbField) {
                    if (!empty($dbField) && isset($row[$colIndex])) $cleanRow[$dbField] = $row[$colIndex];
                }
                if (!empty($cleanRow)) $previewData[] = $cleanRow;
            }
        }
        fclose($handle);
        require_once __DIR__ . '/../Views/import/preview.php';
    }

    public function process() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $filePath = $_SESSION['csv_import_file'] ?? '';
        if (!file_exists($filePath)) {
            $_SESSION['error_message'] = "Session expirée.";
            header('Location: ' . BASE_URL . '/import'); exit;
        }

        $mapping = $_POST['mapping'] ?? [];
        if (is_string($mapping)) $mapping = json_decode($mapping, true);
        
        // Validation Stricte
        $missingFields = [];
        if (array_search('email_parent', $mapping) === false) $missingFields[] = "Email Parent";
        if (array_search('telephone_parent', $mapping) === false) $missingFields[] = "Téléphone Parent";
        if (array_search('cin_parent', $mapping) === false) $missingFields[] = "CIN Parent";

        if (!empty($missingFields)) {
            $_SESSION['error_message'] = "⚠️ <strong>Mapping Incomplet :</strong> " . implode(', ', $missingFields);
            header('Location: ' . BASE_URL . '/import'); exit;
        }

        $importService = new CsvImportService();
        $analysis = $importService->analyzeHeaders($filePath, []);
        $delimiter = $analysis['delimiter'];

        // Validation Contenu
        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 0, $delimiter); 
        $rowNum = 1;
        $emailIndex = array_search('email_parent', $mapping);
        $phoneIndex = array_search('telephone_parent', $mapping);
        $cinIndex   = array_search('cin_parent', $mapping);

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if (empty($row[$emailIndex]) || empty($row[$phoneIndex]) || empty($row[$cinIndex])) {
                fclose($handle);
                $_SESSION['error_message'] = "❌ <strong>Ligne $rowNum :</strong> Données parent manquantes.";
                header('Location: ' . BASE_URL . '/import'); exit;
            }
        }
        fclose($handle);

        try {
            $model = new Etudiant();
            $stats = $importService->importData($filePath, $mapping, $delimiter, $model);
            @unlink($filePath);
            unset($_SESSION['csv_import_file']);
            AuditService::log('IMPORT', "Import Étudiants : {$stats['imported']} ajoutés, {$stats['doublons']} doublons.");
            $_SESSION['flash_message'] = "✅ Import réussi !";
            header('Location: ' . BASE_URL . '/students?success=1');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
            header('Location: ' . BASE_URL . '/import'); exit;
        }
    }

    // --- PARTIE ABSENCES (AVEC LE FIX AUTOMATIQUE) ---

    public function uploadAbsences() {
        $this->checkStaff();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_absences'])) {
            $this->genericUpload('csv_absences', 'csv_absences_file', '/../Views/import/upload_absences.php', $this->absenceColumns, '/../Views/import/mapping_absences.php');
            return;
        }
        require_once __DIR__ . '/../Views/import/upload_absences.php';
    }

    public function previewAbsences() {
        $this->checkStaff();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/import/absences'); exit; }

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
                    if (!empty($dbField) && isset($row[$colIndex])) $cleanRow[$dbField] = $row[$colIndex];
                }
                if (!empty($cleanRow)) $previewData[] = $cleanRow;
            }
        }
        fclose($handle);
        require_once __DIR__ . '/../Views/import/preview_absences.php';
    }

    // --- LE CŒUR DU PROBLÈME CORRIGÉ ICI ---
    public function processAbsences() {
        $this->checkStaff(); 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/import/absences'); exit; }

        $filePath = $_SESSION['csv_absences_file'] ?? '';
        if (!file_exists($filePath)) die("Fichier introuvable.");

        $mapping = isset($_POST['mapping']) ? (is_array($_POST['mapping']) ? $_POST['mapping'] : json_decode($_POST['mapping'], true)) : [];
        
        // Services
        $importService = new CsvImportService();
        $notificationService = new NotificationService(); 
        $etudiantModel = new Etudiant();
        $absenceModel = new Absence();

        $analysis = $importService->analyzeHeaders($filePath, []); 
        $delimiter = $analysis['delimiter'];
        $countImported = 0;
        $countNotifs = 0;

        if (($handle = fopen($filePath, 'r')) !== false) {
            fgetcsv($handle, 0, $delimiter); 
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                
                $absenceData = [];
                foreach ($mapping as $csvIndex => $dbColumn) {
                    if (!empty($dbColumn) && isset($row[$csvIndex])) $absenceData[$dbColumn] = trim($row[$csvIndex]);
                }

                $cne = $absenceData['etudiant_cne'] ?? null;
                if (!$cne || !$etudiantModel->findByCne($cne)) continue; 

                // 1. IMPORT
                if ($absenceModel->create($absenceData)) {
                    $countImported++;

                    // 2. CHECK SEUIL (5, 10, 15...)
                    $totalMonth = $absenceModel->countByMonth($cne);

                    if ($totalMonth > 0 && $totalMonth % 5 == 0) {
                        $student = $etudiantModel->findByCne($cne);

                        if ($student) {
                            $alertLevel = $totalMonth / 5;
                            $notifSent = false; // Pour compter si au moins un truc est parti

                            // --- A. ENVOI WHATSAPP ---
                            $phone = !empty($student['whatsapp_parent']) ? $student['whatsapp_parent'] : ($student['telephone_parent'] ?? null);
                            if (!empty($phone)) {
                                $waMsg = "🚨 *ALERTE ABSENCES*\n\n";
                                $waMsg .= "L'étudiant *{$student['prenom']} {$student['nom']}* a atteint *{$totalMonth} absences*.\n";
                                $waMsg .= "Veuillez consulter l'espace parents.";
                                $resWa = $notificationService->sendWhatsApp($phone, $waMsg);
                                if($resWa['success']) $notifSent = true;
                            }

                            // --- B. ENVOI EMAIL (C'est ce qui manquait !) ---
                            if (!empty($student['email_parent'])) {
                                $subject = "⚠️ Alerte Absences : Niveau $alertLevel atteint";
                                $body = "Bonjour,\n\n";
                                $body .= "Votre enfant {$student['prenom']} {$student['nom']} a cumulé {$totalMonth} absences ce mois-ci.\n";
                                $body .= "Ceci est un avertissement automatique.\n\n";
                                $body .= "Merci de consulter votre espace pour le détail et les justifications.\n";
                                $body .= "L'Administration.";
                                
                                $resEmail = $notificationService->sendEmail($student['email_parent'], $subject, $body);
                                if($resEmail['success']) $notifSent = true;
                            }

                            if ($notifSent) $countNotifs++;
                        }
                    }
                }
            }
            fclose($handle);
        }

        @unlink($filePath);
        unset($_SESSION['csv_absences_file']);
        AuditService::log('IMPORT', "Import Absences : $countImported ajoutées. $countNotifs alertes envoyées.");

        $msg = "Import terminé : $countImported absences ajoutées.";
        if ($countNotifs > 0) {
            $msg .= " 🔔 $countNotifs étudiants ont dépassé le seuil (Parents notifiés).";
        }
        
        $_SESSION['flash_message'] = $msg;
        header('Location: ' . BASE_URL . '/absences/monthly'); 
        exit;
    }
}