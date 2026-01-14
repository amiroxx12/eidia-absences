<?php
namespace App\Services;

// On s'assure que la config globale est chargée
require_once __DIR__ . '/../../config/config.php';

use PDO;
use PDOException;

class DatabaseService {
    private static $instance = null;
    private $connection;

    private function __construct() {
        // ICI : On utilise directement les constantes définies dans config.php
        // Plus besoin d'inclure un fichier db.php séparé
        
        $host = DB_HOST;
        $db   = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // En production, on évite d'afficher le message brut pour ne pas révéler d'infos
            // Mais pour ton debug actuel, c'est utile
            die("❌ Erreur de connexion BDD : " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}