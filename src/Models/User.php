<?php

namespace App\Models;

use PDO;
use App\Config\Database; // Assure-toi que ta classe de connexion s'appelle bien ainsi

class User {
    private $pdo;

    public function __construct() {
        // On récupère l'instance PDO via le Singleton de ta classe Database
        // Si ta classe s'appelle DatabaseService, change 'Database' par 'DatabaseService' ici et au-dessus
        $this->pdo = Database::getConnection();
    }

    /**
     * Trouve un utilisateur par son email
     */
    public function findByEmail(string $email) {
        // Requete préparée pour éviter l'injection SQL
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC); // Retourne un tableau ou false
    }

    /**
     * Crée un nouvel utilisateur (Admin ou Opérateur)
     */
    public function create(array $data): bool {
        // Préparation dynamique des champs (ex: 'nom, email, role')
        $columns = implode(", ", array_keys($data));
        
        // Préparation des placeholders (ex: ':nom, :email, :role')
        $placeholders = ":" . implode(", :", array_keys($data));

        $sql = "INSERT INTO utilisateurs ($columns) VALUES ($placeholders)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            // Si c'est un code erreur "Duplicata" (23000), on retourne false sans planter
            if ($e->getCode() === '23000') {
                return false;
            }
            // Sinon on relance l'erreur pour la voir dans les logs
            throw $e;
        }
    }
}