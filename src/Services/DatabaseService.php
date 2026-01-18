<?php

namespace App\Services; 

// On charge la configuration globale (qui contient DB_USER, DB_PASS, etc.)
require_once __DIR__ . '/../../config/config.php';

use PDO;
use PDOException;

class DatabaseService {
    // Instance unique (Singleton)
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            // ICI : On utilise les constantes de config/credentials.php
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
            
            // Options
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch (PDOException $e) {
            // En prod, on évite d'afficher le message technique complet aux utilisateurs
            die("❌ Erreur de connexion à la base de données. Vérifiez les credentials.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
