<?php
namespace App\Models;

use App\Services\DatabaseService;
use PDO;
use PDOException;

class Etudiant {
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseService::getInstance()->getConnection();
    }

    // ... (La méthode create reste identique, elle est très bien) ...
    public function create(array $data) {
        if (empty($data['cne'])) return false;

        $allowedColumns = [
            'cne', 'nom', 'prenom', 'email', 'telephone', 'classe',
            'nom_parent', 'email_parent', 'telephone_parent', 'whatsapp_parent', 'cin_parent', 'adresse'
        ];

        $cleanData = array_intersect_key($data, array_flip($allowedColumns));
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
                $val = ($value === '' || $value === null) ? null : $value;
                $stmt->bindValue(":$key", $val);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur Import Etudiant: " . $e->getMessage());
            return false;
        }
    }

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

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM etudiants WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- MODIFICATION ICI : Suppression par CNE ---
    public function delete($cne) {
        try {
            $this->pdo->beginTransaction();

            // NUCLÉAIRE : On désactive la sécurité des clés étrangères
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Suppression de l'étudiant
            $stmt = $this->pdo->prepare("DELETE FROM etudiants WHERE cne = :cne");
            $success = $stmt->execute([':cne' => $cne]);

            // Nettoyage des absences fantômes
            $query = $this->pdo->query("SHOW TABLES LIKE 'absences_%'");
            $tables = $query->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                $sql = "DELETE FROM `$table` WHERE etudiant_cne = :cne";
                $stmtAbs = $this->pdo->prepare($sql);
                $stmtAbs->execute([':cne' => $cne]);
            }

            // On remet la sécurité et on valide
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            $this->pdo->commit();

            return $success;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            return false;
        }
    }

    public function update($id, $data) {
        $fields = [];
        foreach ($data as $key => $val) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE etudiants SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function getDistinctClasses() {
        $stmt = $this->pdo->query("SELECT DISTINCT classe FROM etudiants WHERE classe IS NOT NULL AND classe != '' ORDER BY classe ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function countAll() {
        return $this->pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
    }
    
    public function countClasses() {
        return $this->pdo->query("SELECT COUNT(DISTINCT classe) FROM etudiants")->fetchColumn();
    }

    // Consolidé : findAllWithStats est identique à findAll maintenant
    public function findAllWithStats($filters = []) {
        return $this->findAll($filters);
    }

    public function findByCne($cne) {
        $stmt = $this->pdo->prepare("SELECT * FROM etudiants WHERE cne = :cne");
        $stmt->bindParam(':cne', $cne);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}