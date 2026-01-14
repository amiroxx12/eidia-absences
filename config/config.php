<?php

// 1. GESTION DES ERREURS (Garde ça activé tant que le site n'est pas stable)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. DÉFINITION DE LA RACINE DU PROJET
// Si ce fichier est dans /var/www/site/public_html/config/config.php
// Alors ROOT_PATH devient /var/www/site/public_html
define('ROOT_PATH', dirname(__DIR__)); 

// 3. CHARGEMENT DU .ENV (Cœur du système)
// On cherche le fichier .env à la racine du site
$envFile = ROOT_PATH . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) continue;

        // Parser Clé=Valeur
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // On injecte dans les variables d'environnement PHP et Serveur
            // C'est ça qui permet à getenv() de fonctionner partout
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// 4. DÉFINITION DES CONSTANTES GLOBALES (Le pont vers ta BDD)
// On regarde d'abord dans getenv() (Apache/Systeme), sinon dans $_ENV (Fichier .env), sinon valeur par défaut
define('DB_HOST', getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost'));
define('DB_NAME', getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'eidia_absences'));
define('DB_USER', getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root')); // C'est ici que ça bloquait avant
define('DB_PASS', getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? ''));

// Twilio / WhatsApp
define('TWILIO_SID', getenv('TWILIO_SID') ?: ($_ENV['TWILIO_SID'] ?? ''));
define('TWILIO_TOKEN', getenv('TWILIO_TOKEN') ?: ($_ENV['TWILIO_TOKEN'] ?? ''));
define('TWILIO_FROM', getenv('TWILIO_FROM') ?: ($_ENV['TWILIO_FROM'] ?? ''));

// 5. URL DE BASE (Dynamique)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']); 
$scriptDir = rtrim($scriptDir, '/\\');
define('BASE_URL', $protocol . "://" . $host . $scriptDir);

// 6. SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}