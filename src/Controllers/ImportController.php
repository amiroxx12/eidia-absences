<?php 
namespace App\Controllers;

use App\Services\CsvImportService;
use App\Models\Etudiant;
use App\Models\Absence; // <--- Ajout du modèle Absence

class ImportController {
    
    // Config pour les étudiants
    private $authorizedColumns = [
        'cne', 'nom', 'prenom', 'email', 'telephone', 'classe',            
        'email_parent', 'telephone_parent', 'whatsapp_parent', 'nom_parent', 'adresse'
    ];

    // Config pour les absences (Phase 3)
    private $absenceColumns = [
        'etudiant_cne', 'date_seance', 'heure_debut', 'matiere'
    ];

    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function index() {
        $this->checkAuth();
        require_once __DIR__ . '/../Views/import/upload.php';
    }

    // =========================================================================
    // PARTIE 1 : GESTION DES ÉTUDIANTS (Phase 2 - Complexe avec Mapping UI)
    // =========================================================================

    public function upload() {
        $this->checkAuth();
        // ... (Ton code de nettoyage inchangé) ...
        $uploadDir = __DIR__ . '/../../public/uploads/';
        // Nettoyage des vieux fichiers temporaires
        if (is_dir($uploadDir)) {
            $files = glob($uploadDir . 'temp_import_*.csv');
            $now = time();
            foreach ($files as $file) {
                if (is_file($file) && ($now - filemtime($file) >= 3600)) @unlink($file);
            }
        }

        $error = null;

        // Validation standard
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_file'])) {
            $error = "Veuillez sélectionner un fichier.";
        } elseif ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $error = "Erreur lors du transfert (Code: " . $_FILES['csv_file']['error'] . ")";
        } else {
            $file = $_FILES['csv_file'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if(strtolower($ext) !== 'csv') {
                $error = "Extension invalide. Le fichier doit finir par .csv";
            }
            // (Ta vérification MIME ici...)
        }

        if ($error) {
            require_once __DIR__ . '/../Views/import/upload.php';
            return;
        }

        // Upload physique
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $tmpFilePath = $uploadDir . 'temp_import_' . time() . '_' . bin2hex(random_bytes(4)) . '.csv';
        
        if (!move_uploaded_file($file['tmp_name'], $tmpFilePath)) {
            $error = "Impossible d'enregistrer le fichier.";
            require_once __DIR__ . '/../Views/import/upload.php';
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['csv_import_file'] = $tmpFilePath;

        // Analyse pour le Mapping Visuel
        $importService = new CsvImportService();
        try {
            $analysis = $importService->analyzeHeaders($tmpFilePath, $this->authorizedColumns);
            
            $csvHeaders = $analysis['csv_headers'];
            $suggestedMapping = $analysis['suggested_mapping'];
            $detectedDelimiter = $analysis['delimiter'];
            $dbColumns = $this->authorizedColumns; 
            
            require_once __DIR__ . '/../Views/import/mapping.php';

        } catch (\Exception $e) {
            @unlink($tmpFilePath);
            $error = "Erreur d'analyse : " . $e->getMessage();
            require_once __DIR__ . '/../Views/import/upload.php';
        }
    }

    public function preview() {
        // ... (Ton code preview inchangé) ...
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
        if (session_status() === PHP_SESSION_NONE) session_start();

        $filePath = $_SESSION['csv_import_file'] ?? null;
        $mapping = $_POST['mapping'] ?? [];
        $delimiter = $_POST['delimiter'] ?? ';';
        
        if (!$filePath || !file_exists($filePath)) die("Session expirée.");

        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 0, $delimiter);
        
        $previewData = [];
        for ($i = 0; $i < 5; $i++) {
            $row = fgetcsv($handle, 0, $delimiter);
            if ($row !== false) $previewData[] = $row;
            else break; 
        }
        fclose($handle);
        require_once __DIR__ . '/../Views/import/preview.php';
    }

    public function process() {
        // ... (Ton code process Étudiants inchangé) ...
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') header('Location: ' . BASE_URL . '/import');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $filePath = $_SESSION['csv_import_file'] ?? '';
        if (!file_exists($filePath)) die("Erreur : Fichier introuvable.");

        if (isset($_POST['mapping'])) {
            $mapping = is_array($_POST['mapping']) ? $_POST['mapping'] : json_decode($_POST['mapping'], true);
        } else {
            $mapping = [];
        }

        $importService = new CsvImportService();
        $analysis = $importService->analyzeHeaders($filePath, []); // Juste pour le delimiter
        $delimiter = $analysis['delimiter'];

        $etudiantModel = new Etudiant(); 

        try {
            // Import Etudiants
            $stats = $importService->importData($filePath, $mapping, $delimiter, $etudiantModel);
            
            if (file_exists($filePath)) @unlink($filePath);
            unset($_SESSION['csv_import_file']);

            // Note: $stats est maintenant un array ['imported', 'doublons']
            $msg = "Import réussi : " . $stats['imported'] . " étudiants ajoutés/mis à jour.";
            header('Location: ' . BASE_URL . '/students?success=' . urlencode($msg));
            exit;

        } catch (\Exception $e) {
            die("Erreur critique : " . $e->getMessage());
        }
    }
// =========================================================================
    // PARTIE 2 : GESTION DES ABSENCES (Phase 3 - Import Complet)
    // =========================================================================

    /**
     * Étape 1 : Upload et Analyse des entêtes
     */
    public function uploadAbsences() {
        $this->checkAuth();
        
        // Variable pour stocker les erreurs d'upload
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_absences'])) {
            
            $file = $_FILES['csv_absences'];
            
            // 1. Erreur technique upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = "Erreur technique lors du transfert (Code: " . $file['error'] . ")";
            }
            // 2. Vérification Extension
            elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
                $error = "Format refusé : Seuls les fichiers .csv sont acceptés.";
            }
            else {
                // 3. Vérification MIME Type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedMimes = [
                    'text/csv', 'text/plain', 'text/x-csv', 'application/vnd.ms-excel', 
                    'application/csv', 'application/x-csv', 'text/comma-separated-values',
                    'text/x-comma-separated-values'
                ];

                if (!in_array($mimeType, $allowedMimes)) {
                    $error = "Sécurité : Le fichier semble suspect ($mimeType). Veuillez vérifier qu'il s'agit bien d'un CSV.";
                }
            }

            // --- Si aucune erreur de sécurité, on tente le traitement ---
            if (!$error) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $tmpFilePath = $uploadDir . 'absences_' . time() . '_' . bin2hex(random_bytes(4)) . '.csv';
                
                if (move_uploaded_file($file['tmp_name'], $tmpFilePath)) {
                    
                    // Mise en session
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    $_SESSION['csv_absences_file'] = $tmpFilePath;

                    // Analyse
                    $importService = new \App\Services\CsvImportService();
                    try {
                        $analysis = $importService->analyzeHeaders($tmpFilePath, $this->absenceColumns);
                        
                        // Variables pour la vue suivante
                        $csvHeaders = $analysis['csv_headers'];
                        $suggestedMapping = $analysis['suggested_mapping'];
                        $detectedDelimiter = $analysis['delimiter'];
                        $dbColumns = $this->absenceColumns; 
                        
                        require_once __DIR__ . '/../Views/import/mapping_absences.php';
                        exit; // On s'arrête là car on a chargé une autre vue

                    } catch (\Exception $e) {
                        @unlink($tmpFilePath);
                        $error = "Erreur d'analyse du fichier : " . $e->getMessage();
                    }
                } else {
                    $error = "Impossible d'enregistrer le fichier sur le serveur (Problème de permissions ?).";
                }
            }
        }
        
        // S'il y a une erreur (ou si c'est le premier chargement), on affiche la vue d'upload
        // La vue aura accès à la variable $error
        require_once __DIR__ . '/../Views/import/upload_absences.php';
    }

    /**
     * Étape 2 : Prévisualisation (Intermédiaire)
     */
    public function previewAbsences() {
        $this->checkAuth();
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/import/absences');
            exit;
        }

        $filePath = $_SESSION['csv_absences_file'] ?? '';
        if (!file_exists($filePath)) die("Fichier expiré.");

        // On récupère le mapping choisi par l'utilisateur
        $mapping = $_POST['mapping'] ?? [];
        $delimiter = $_POST['delimiter'] ?? ';';

        // On lit les 5 premières lignes pour montrer à quoi ça ressemble
        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 0, $delimiter); // On saute l'entête
        
        $previewData = [];
        for ($i = 0; $i < 5; $i++) {
            $row = fgetcsv($handle, 0, $delimiter);
            if ($row !== false) {
                // On reconstruit une ligne "propre" basée sur le mapping
                $cleanRow = [];
                foreach ($mapping as $colIndex => $dbField) {
                    if (!empty($dbField) && isset($row[$colIndex])) {
                        $cleanRow[$dbField] = $row[$colIndex]; 
                    }
                }
                if (!empty($cleanRow)) $previewData[] = $cleanRow;
            } else {
                break;
            }
        }
        fclose($handle);

        require_once __DIR__ . '/../Views/import/preview_absences.php';
    }

    /**
     * Étape 3 : Traitement final (Insertion BDD)
     */
    public function processAbsences() {
        $this->checkAuth();
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/import/absences');
            exit;
        }

        $filePath = $_SESSION['csv_absences_file'] ?? '';
        if (!file_exists($filePath)) die("Erreur : Fichier expiré ou introuvable.");

        // GESTION DU MAPPING (Venant de la Preview - JSON)
        if (isset($_POST['mapping'])) {
            if (!is_array($_POST['mapping'])) {
                $mapping = json_decode($_POST['mapping'], true);
            } else {
                $mapping = $_POST['mapping'];
            }
        } else {
            $mapping = [];
        }

        // Récupération du délimiteur
        $importService = new CsvImportService();
        $analysis = $importService->analyzeHeaders($filePath, []); 
        $delimiter = $analysis['delimiter'];

        $absenceModel = new Absence();

        try {
            // Lancement de l'import
            $stats = $importService->importData($filePath, $mapping, $delimiter, $absenceModel);

            // Nettoyage
            @unlink($filePath);
            unset($_SESSION['csv_absences_file']);

            // Feedback
            $msg = "Import terminé : " . $stats['imported'] . " absences ajoutées.";
            if ($stats['doublons'] > 0) {
                $msg .= " (" . $stats['doublons'] . " doublons ignorés)";
            }
            
            $_SESSION['flash_message'] = $msg;
            header('Location: ' . BASE_URL . '/dashboard'); 
            exit;

        } catch (\Exception $e) {
            die("Erreur Import Absences : " . $e->getMessage());
        }
    }
}