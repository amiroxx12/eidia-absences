<?php
// 1. Config & Debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
// 3. ROUTAGE PHYSIQUE (INDÉPENDANT DE CONFIG.PHP)
// ============================================================

// On demande à PHP : "Dans quel dossier physique suis-je ?"
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);

// On demande à PHP : "Quelle est l'URL demandée ?"
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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

// Si c'est vide, c'est la racine
if ($path === '') {
    $path = '/';
}

// ============================================================
// 4. LISTE DES ROUTES
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
    
    // Phase 3 : Import Absences (NOUVEAU)
    '/import/absences' => ['App\Controllers\ImportController', 'uploadAbsences'],
    '/import/preview-absences' => ['App\Controllers\ImportController', 'previewAbsences'],
    '/import/process-absences' => ['App\Controllers\ImportController', 'processAbsences'],
    
    // Gestion Etudiants
    '/students' => ['App\Controllers\StudentController', 'index'],
    '/students/delete' => ['App\Controllers\StudentController', 'delete'],
    '/students/details' => ['App\Controllers\StudentController', 'details'],
    // '/students/details' => ['App\Controllers\StudentController', 'details'], // À décommenter quand la méthode sera créée
];

// ============================================================
// 5. EXÉCUTION
// ============================================================
if (array_key_exists($path, $routes)) {
    $controllerName = $routes[$path][0];
    $methodName = $routes[$path][1];

    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        if (method_exists($controller, $methodName)) {
            $controller->$methodName();
        } else {
            die("Erreur : Méthode $methodName introuvable dans $controllerName.");
        }
    } else {
        die("Erreur : Classe $controllerName introuvable. Vérifie le namespace et le nom du fichier.");
    }
} else {
    // Debuggage 404
    http_response_code(404);
    echo "<h1>404 - Page non trouvée</h1>";
    echo "<ul>";
    echo "<li>Dossier détecté : <strong>$scriptDir</strong></li>";
    echo "<li>URL demandée : <strong>$requestUri</strong></li>";
    echo "<li>Route calculée : <strong>[$path]</strong></li>";
    echo "</ul>";
    echo "<p>Vérifiez que la route est bien déclarée dans le tableau <code>\$routes</code> de public/index.php</p>";
}