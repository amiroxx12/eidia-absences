<?php
// config/config.php

// 1. GESTION DES ERREURS
ini_set('display_error', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. CHEMIN RACINE
define('ROOT_PATH', dirname(__DIR__)); 

// 3. CHARGEMENT LOCAL (Pour ton PC uniquement)
// On charge le .env s'il existe (ignoré par Git)
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
            }
        }
    }
}

// 4. DÉFINITION DES CONSTANTES (Le Cœur du Système)
// getenv() récupère les infos sécurisées d'Apache sur la VM.
// $_ENV[] récupère les infos de ton .env sur ton PC.

define('DB_HOST', getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost'));
define('DB_NAME', getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'eidia_absences'));
define('DB_USER', getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root'));
define('DB_PASS', getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? ''));

// Twilio
define('TWILIO_SID', getenv('TWILIO_SID') ?: ($_ENV['TWILIO_SID'] ?? ''));
define('TWILIO_TOKEN', getenv('TWILIO_TOKEN') ?: ($_ENV['TWILIO_TOKEN'] ?? ''));
define('TWILIO_FROM', getenv('TWILIO_FROM') ?: ($_ENV['TWILIO_FROM'] ?? ''));

// 5. URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']); 
$scriptDir = rtrim($scriptDir, '/\\');
define('BASE_URL', $protocol . "://" . $host . $scriptDir);

// 6. SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}