<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    
    // Instance unique de la connexion (Singleton)
    private static $instance = null;
    private $pdo;

    private function __construct() {
        // --- TES PARAMÈTRES DE CONNEXION ---
        $host = 'localhost';
        $dbname = 'eidia_absences';
        $username = 'root'; // Par défaut sur XAMPP
        $password = '';     // Par défaut sur XAMPP (vide)

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            
            // Options importantes pour voir les erreurs SQL
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            die("Erreur fatale de connexion BDD : " . $e->getMessage());
        }
    }

    // C'est cette méthode statique qu'on appelle partout : Database::getConnection()
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}