<?php

namespace App\Services; 

use PDO;
use PDOException;

class DatabaseService {
    // Instance unique de la classe (Singleton)
    private static $instance = null;
    private $pdo;

    // Configuration de la base de données (XAMPP par défaut)
    private $host = '127.0.0.1';
    private $db_name = 'eidia_absences'; // Vérifie que c'est bien le nom de ta BDD dans PHPMyAdmin
    private $username = 'root';
    private $password = ''; // Sur XAMPP Mac, c'est souvent vide par défaut

    // Le constructeur est privé pour empêcher de faire "new DatabaseService()"
    private function __construct() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            
            // Options pour voir les erreurs SQL et bien gérer les données
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            die("Erreur de connexion BDD : " . $e->getMessage());
        }
    }

    // Méthode statique pour récupérer l'unique instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DatabaseService();
        }
        return self::$instance;
    }

    // Pour récupérer l'objet PDO
    public function getConnection() {
        return $this->pdo;
    }
}