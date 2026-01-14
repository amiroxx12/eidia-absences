<?php
namespace App\Models;

use App\Services\DatabaseService;
use PDO;
use PDOException;

class Etudiant {
    private $pdo;

    // Liste blanche des colonnes modifiables (Sécurité)
    private $allowedColumns = [
        'cne', 'nom', 'prenom', 'email', 'telephone', 'classe',
        'nom_parent', 'email_parent', 'telephone_parent', 'whatsapp_parent', 'cin_parent', 'adresse'
    ];

    public function __construct() {
        $this->pdo = DatabaseService::getInstance()->getConnection();
    }

    public function create(array $data) {
        if (empty($data['cne'])) return false;

        $cleanData = array_intersect_key($data, array_flip($this->allowedColumns));
        
        $columns = implode(", ", array_keys($cleanData));
        $placeholders = ":" . implode(", :", array_keys($cleanData));
        
        $updates = [];
        foreach ($cleanData as $key => $value) {
            if ($key !== 'cne') {
                $updates[] = "$key = VALUES($key)";
            }
        }
        $updateString = implode(", ", $updates);

        $sql = "INSERT INTO etudiants ($columns) VALUES ($placeholders) 
                ON DUPLICATE KEY UPDATE $updateString";

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($cleanData as $key => $value) {
                $val = ($value === '' || $value === null) ? null : trim($value);
                $stmt->bindValue(":$key", $val);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur Import Etudiant: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        $fields = [];
        $cleanData = [];
        
        foreach ($data as $key => $val) {
            if (in_array($key, $this->allowedColumns)) {
                $fields[] = "$key = :$key";
                $cleanData[$key] = $val;
            }
        }
        
        if (empty($fields)) return false;

        $sql = "UPDATE etudiants SET " . implode(', ', $fields) . " WHERE id = :id";
        $cleanData['id'] = $id;
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($cleanData);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($cne) {
        try {
            $this->pdo->beginTransaction();
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

            $stmt = $this->pdo->prepare("DELETE FROM etudiants WHERE cne = :cne");
            $stmt->execute([':cne' => $cne]);

            $query = $this->pdo->query("SHOW TABLES LIKE 'absences_%'");
            $tables = $query->fetchAll(PDO::FETCH_COLUMN);
            $query->closeCursor(); 

            foreach ($tables as $table) {
                $stmtAbs = $this->pdo->prepare("DELETE FROM `$table` WHERE etudiant_cne = :cne");
                $stmtAbs->execute([':cne' => $cne]);
            }

            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            return false;
        }
    }

    // --- RECHERCHE ---

    public function findAll($filters = []) {
        $sql = "SELECT * FROM etudiants WHERE 1=1";
        $params = [];

        if (!empty($filters['classe'])) {
            $sql .= " AND classe = :classe";
            $params[':classe'] = $filters['classe'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (nom LIKE :search OR prenom LIKE :search OR cne LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY classe ASC, nom ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- 👇 LE FIX EST ICI : On remet la méthode manquante ---
    public function findAllWithStats($filters = []) {
        return $this->findAll($filters);
    }
    // ---------------------------------------------------------

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM etudiants WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByCne($cne) {
        $stmt = $this->pdo->prepare("SELECT * FROM etudiants WHERE cne = :cne");
        $stmt->execute([':cne' => $cne]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDistinctClasses() {
        return $this->pdo->query("SELECT DISTINCT classe FROM etudiants WHERE classe != '' ORDER BY classe ASC")->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function countAll() {
        return $this->pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
    }
    
    public function countClasses() {
        return $this->pdo->query("SELECT COUNT(DISTINCT classe) FROM etudiants")->fetchColumn();
    }
}