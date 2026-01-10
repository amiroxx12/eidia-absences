<?php

namespace App\Models;

use PDO;
use App\Config\Database; 

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function findByEmail(string $email) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- NOUVELLES MÉTHODES ---

    /**
     * Récupère tous les utilisateurs sauf les parents (souvent trop nombreux)
     * Ou tous si tu veux. Ici je filtre pour la gestion Admin.
     */
    public function getAllStaff() {
        $sql = "SELECT * FROM utilisateurs WHERE role IN ('admin', 'operateur') ORDER BY role ASC, nom ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO utilisateurs ($columns) VALUES ($placeholders)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') return false;
            throw $e;
        }
    }

    public function delete($id) {
        // Sécurité : on empêche de supprimer un Admin via cette méthode simple
        // (à affiner selon tes besoins)
        $stmt = $this->pdo->prepare("DELETE FROM utilisateurs WHERE id = :id AND role != 'admin'");
        return $stmt->execute([':id' => $id]);
    }
}