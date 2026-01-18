<?php
// Pour Debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    die("Erreur critique : Le dossier 'vendor' est introuvable.");
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

// ROUTAGE
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = $_SERVER['SCRIPT_NAME'];

if (strpos($requestUri, $scriptName) !== false) {
    $path = substr($requestUri, strlen($scriptName));
} else {
    $scriptDir = dirname($scriptName);
    $path = substr($requestUri, strlen($scriptDir));
}

$path = '/' . ltrim($path, '/');
if ($path === '' || $path === '/') { $path = '/'; }

// 4. LISTE DES ROUTES
$routes = [
    // --- ACCÈS PUBLIC (Par défaut = Parents) ---
    '/' => ['App\Controllers\ParentController', 'showLogin'], 
    
    // --- ACCÈS ADMIN (Caché) ---
    '/admin' => ['App\Controllers\AuthController', 'login'], // <--- C'est ici que tu te connectes maintenant !
    '/login' => ['App\Controllers\AuthController', 'login'], // On garde pour compatibilité
    '/logout' => ['App\Controllers\AuthController', 'logout'],
    
    // Dashboard Admin
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
    
    // Rapports
    '/absences/monthly' => ['App\Controllers\AbsenceController', 'monthlyView'],
    '/absences/export' => ['App\Controllers\AbsenceController', 'export'],
    '/absences/decide-justification' => ['App\Controllers\AbsenceController', 'handleJustificationDecision'],

    // Phase 5 : Espace Parents
    '/parent/verify'       => ['App\Controllers\ParentController', 'verifyLink'],
    '/parent/check-cin'    => ['App\Controllers\ParentController', 'handleLogin'],
    '/parent/dashboard'    => ['App\Controllers\ParentController', 'dashboard'],
    '/parent/set-password' => ['App\Controllers\ParentController', 'showSetPassword'],
    '/parent/save-password'=> ['App\Controllers\ParentController', 'savePassword'],
    '/parent/login'        => ['App\Controllers\ParentController', 'showLogin'],
    '/parent/login/submit' => ['App\Controllers\ParentController', 'handleStandardLogin'],
    '/parent/justify' => ['App\Controllers\ParentController', 'handleJustification'],
    '/parent/logout'       => ['App\Controllers\ParentController', 'logout'],

    // Gestion Utilisateurs (Admin)
    '/users' => ['App\Controllers\UserController', 'index'],
    '/users/create' => ['App\Controllers\UserController', 'create'],
    '/users/delete' => ['App\Controllers\UserController', 'delete'],
    '/users/parents' => ['App\Controllers\UserController', 'parents'],
    '/users/resend-magic-link' => ['App\Controllers\UserController', 'resendMagicLink'],

    //Logs
    '/logs' => ['App\Controllers\LogController', 'index'],

    // Configuration
    '/settings' => ['App\Controllers\SettingsController', 'index'],
    '/settings/save' => ['App\Controllers\SettingsController', 'save'],
    '/settings/test' => ['App\Controllers\SettingsController', 'test'], 
    '/settings/generateParentLinks' => ['App\Controllers\SettingsController', 'generateParentLinks'],
];

// 5. EXÉCUTION
if (array_key_exists($path, $routes)) {
    $controllerName = $routes[$path][0];
    $methodName = $routes[$path][1];

    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        if (method_exists($controller, $methodName)) {
            $controller->$methodName();
        } else {
            die("Erreur : Méthode <strong>$methodName</strong> introuvable.");
        }
    } else {
        die("Erreur : Classe <strong>$controllerName</strong> introuvable.");
    }
} else {
    // Redirection soft vers la racine si page inconnue
    header('Location: ' . BASE_URL . '/');
    exit;
}