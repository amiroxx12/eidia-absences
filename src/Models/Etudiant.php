<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Services\DatabaseService;

class Etudiant {
    
    private PDO $pdo;

    public function __construct(PDO $pdo = null) {
        $this->pdo = $pdo ?? DatabaseService::getConnection();
    }

    // Insère un étudiant dynamiquement en fonction des colonnes fournies.
    public function create(array $data): bool {
        if (empty($data)) {
            return false;
        }

        // 1. Construction dynamique de la requête SQL
        // Permet d'insérer seulement les champs présents dans le CSV (nom, cne, etc.)
        $columns = array_keys($data);
        
        $columnList = implode(', ', $columns);
        $placeholders = ':' . implode(', :', $columns);

        $sql = "INSERT INTO etudiants ($columnList) VALUES ($placeholders)";

        try {
            $stmt = $this->pdo->prepare($sql);
            
            // 2. Exécution sécurisée (Prepared Statement)
            return $stmt->execute($data);

        } catch (PDOException $e) {
            // Code SQLState 23000 = Violation de contrainte d'intégrité (Duplicate Entry)
            // C'est le cas si le CNE existe déjà. On retourne false proprement.
            if ($e->getCode() === '23000') {
                return false; 
            }
            
            // Pour toute autre erreur technique (syntaxe, connexion...), on remonte l'exception
            throw $e;
        }
    }

    //Trouve un étudiant par son CNE.
    public function findByCne(string $cne): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM etudiants WHERE cne = :cne LIMIT 1");
        $stmt->execute(['cne' => $cne]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Récupère tous les étudiants (pour l'affichage liste).
    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM etudiants ORDER BY nom ASC, prenom ASC");
        return $stmt->fetchAll();
    }
}