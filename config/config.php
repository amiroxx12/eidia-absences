<?php
// config/config.php

// 1. GESTION DES ERREURS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. RACINE
define('ROOT_PATH', dirname(__DIR__)); 

// 3. CHARGEMENT DES SECRETS (Priorité absolue)
// On cherche le fichier credentials.php dans le même dossier que ce fichier config.php
$credentialsFile = __DIR__ . '/credentials.php';

if (file_exists($credentialsFile)) {
    require_once $credentialsFile;
}

// 4. CHARGEMENT .ENV (Fallback pour le local)
// Si les constantes ne sont pas définies par credentials.php (donc on est en local), on cherche .env
if (!defined('DB_USER')) {
    $envFile = ROOT_PATH . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $_ENV[trim($name)] = trim($value);
            }
        }
    }
}

// 5. DÉFINITION PAR DÉFAUT (Au cas où)
// On utilise 'defined' pour ne pas écraser ce que credentials.php a mis.
if (!defined('DB_HOST')) define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', $_ENV['DB_NAME'] ?? 'eidia_absences');
if (!defined('DB_USER')) define('DB_USER', $_ENV['DB_USER'] ?? 'root');
if (!defined('DB_PASS')) define('DB_PASS', $_ENV['DB_PASS'] ?? '');

if (!defined('TWILIO_SID')) define('TWILIO_SID', $_ENV['TWILIO_SID'] ?? '');
if (!defined('TWILIO_TOKEN')) define('TWILIO_TOKEN', $_ENV['TWILIO_TOKEN'] ?? '');
if (!defined('TWILIO_FROM')) define('TWILIO_FROM', $_ENV['TWILIO_FROM'] ?? '');

// 6. URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']); 
$scriptDir = rtrim($scriptDir, '/\\');
define('BASE_URL', $protocol . "://" . $host . $scriptDir);

// 7. SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}