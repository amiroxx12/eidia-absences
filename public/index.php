<?php
// 1. Config & Debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    die("Erreur critique : Le dossier 'vendor' est introuvable. As-tu lancé 'composer require phpmailer/phpmailer' ?");
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// 2. Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

// ============================================================
// 3. ROUTAGE PHYSIQUE
// ============================================================

$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // On ignore les paramètres ?channel=...

// Correction Windows/Mac
$scriptDir = str_replace('\\', '/', $scriptDir);

// --- LE CALCUL ---
if (stripos($requestUri, $scriptDir) === 0) {
    $path = substr($requestUri, strlen($scriptDir));
} else {
    $path = $requestUri;
}

// Nettoyage final
$path = str_replace('/index.php', '', $path);
$path = rtrim($path, '/');

if ($path === '') {
    $path = '/';
}

// ============================================================
// 4. LISTE DES ROUTES (UNIFORMISÉE)
// ============================================================
$routes = [
    // Auth
    '/' => ['App\Controllers\AuthController', 'login'],
    '/login' => ['App\Controllers\AuthController', 'login'],
    '/logout' => ['App\Controllers\AuthController', 'logout'],
    
    // Dashboard
    '/dashboard' => ['App\Controllers\DashboardController', 'index'],
    
    // Phase 2 : Import Etudiants
    '/import' => ['App\Controllers\ImportController', 'index'],
    '/import/upload' => ['App\Controllers\ImportController', 'upload'],
    '/import/preview' => ['App\Controllers\ImportController', 'preview'],
    '/import/process' => ['App\Controllers\ImportController', 'process'],
    
    // Phase 3 : Import Absences
    '/absences' => ['App\Controllers\AbsenceController', 'monthlyView'],
    '/import/absences' => ['App\Controllers\ImportController', 'uploadAbsences'],
    '/import/previewAbsences' => ['App\Controllers\ImportController', 'previewAbsences'],
    '/import/processAbsences' => ['App\Controllers\ImportController', 'processAbsences'],
    '/absences/notifyManual' => ['App\Controllers\AbsenceController', 'notifyManual'],
    
    // Gestion Etudiants
    '/students' => ['App\Controllers\StudentController', 'index'],
    '/students/delete' => ['App\Controllers\StudentController', 'delete'],
    '/students/details' => ['App\Controllers\StudentController', 'details'],
    
    // Rapports et Exports
    '/absences/monthly' => ['App\Controllers\AbsenceController', 'monthlyView'],
    '/absences/export' => ['App\Controllers\AbsenceController', 'export'],

    // Gestion Utilisateurs (Admin)
    '/users' => ['App\Controllers\UserController', 'index'],
    '/users/create' => ['App\Controllers\UserController', 'create'],
    '/users/delete' => ['App\Controllers\UserController', 'delete'],

    // --- CONFIGURATION (CORRIGÉ) ---
    '/settings' => ['App\Controllers\SettingsController', 'index'],
    '/settings/save' => ['App\Controllers\SettingsController', 'save'],
    '/settings/test' => ['App\Controllers\SettingsController', 'test'], 
];

// ============================================================
// 5. EXÉCUTION
// ============================================================
if (array_key_exists($path, $routes)) {
    // On récupère le Controller et la Méthode via les index 0 et 1
    $controllerName = $routes[$path][0];
    $methodName = $routes[$path][1];

    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        if (method_exists($controller, $methodName)) {
            $controller->$methodName();
        } else {
            die("Erreur : Méthode <strong>$methodName</strong> introuvable dans <strong>$controllerName</strong>.");
        }
    } else {
        die("Erreur : Classe <strong>$controllerName</strong> introuvable. Vérifie le namespace et le nom du fichier.");
    }
} else {
    // Debuggage 404
    http_response_code(404);
    echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>";
    echo "<h1>404 - Page non trouvée</h1>";
    echo "<p>Route calculée : <strong>" . htmlspecialchars($path) . "</strong></p>";
    echo "<a href='" . BASE_URL . "/dashboard'>Retour au tableau de bord</a>";
    echo "</div>";
}