<?php 
namespace App\Controllers;

use App\Services\CsvImportService;
use App\Models\ImportConfiguration;
use App\Models\Etudiant; 

class ImportController {
    
    private $authorizedColumns = [
        'cne', 'nom', 'prenom', 'email', 'telephone', 'classe',            
        'email_parent', 'telephone_parent', 'whatsapp_parent', 'nom_parent', 'adresse'
    ];

    public function index() {
        require_once __DIR__ . '/../Views/import/upload.php';
    }

    public function upload() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_file'])) {
            die("Erreur : Aucun fichier envoyé");
        }

        $file = $_FILES['csv_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if(strtolower($ext) !== 'csv') die("Erreur : le fichier doit être un CSV.");

        $uploadDir = __DIR__ . '/../../public/uploads/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $tmpFilePath = $uploadDir . 'temp_import_' . time() . '.csv';
        if (!move_uploaded_file($file['tmp_name'], $tmpFilePath)) die("Erreur upload.");

        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['csv_import_file'] = $tmpFilePath;

        $importService = new CsvImportService();
        try {
            $analysis = $importService->analyzeHeaders($tmpFilePath, $this->authorizedColumns);
            
            $csvHeaders = $analysis['csv_headers'];
            $suggestedMapping = $analysis['suggested_mapping'];
            $detectedDelimiter = $analysis['delimiter'];
            $dbColumns = $this->authorizedColumns; 
            
            require_once __DIR__ . '/../Views/import/mapping.php';

        } catch (\Exception $e) {
            die("Erreur d'analyse : " . $e->getMessage());
        }
    }

    public function preview() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/import'); // CORRIGÉ
            exit;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();

        $filePath = $_SESSION['csv_import_file'] ?? null;
        $mapping = $_POST['mapping'] ?? [];
        $delimiter = $_POST['delimiter'] ?? ';';
        
        $saveConfig = isset($_POST['save_config']);
        $configName = $_POST['config_name'] ?? '';

        if (!$filePath || !file_exists($filePath)) die("Session expirée.");

        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 0, $delimiter);
        
        $previewRows = [];
        for ($i = 0; $i < 5; $i++) {
            $row = fgetcsv($handle, 0, $delimiter);
            if ($row !== false) $previewRows[] = $row;
            else break; 
        }
        fclose($handle);

        $finalMapping = $mapping; 
        require_once __DIR__ . '/../Views/import/preview.php';
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/import'); // CORRIGÉ
            exit;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['csv_import_file']) || !file_exists($_SESSION['csv_import_file'])) {
            die("Erreur : Fichier expiré.");
        }

        $filePath = $_SESSION['csv_import_file'];
        $mapping = $_POST['mapping'] ?? []; 
        $delimiter = $_POST['delimiter'] ?? ';'; 

        if (isset($_POST['save_config']) && !empty($_POST['config_name'])) {
            $configModel = new ImportConfiguration();
            $configModel->save($_POST['config_name'], $mapping);
        }

        $importService = new CsvImportService();
        $etudiantModel = new Etudiant(); 

        try {
            $count = $importService->importData($filePath, $mapping, $delimiter, $etudiantModel);
            
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            unset($_SESSION['csv_import_file']);

            echo "<div style='padding: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px;'>
                    <strong>Succès !</strong> $count étudiants importés.<br><br>
                    <a href='".BASE_URL."/dashboard' style='text-decoration: underline; color: #155724;'>Retour au tableau de bord</a>
                  </div>";

        } catch (\Exception $e) {
            die("Erreur critique : " . $e->getMessage());
        }
    }
}