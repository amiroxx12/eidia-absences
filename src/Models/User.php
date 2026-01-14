<?php

namespace App\Models;

use PDO;
use App\Services\DatabaseService; // <--- C'EST ICI QUE ÇA CHANGE

class User {
    private $pdo;

    public function __construct() {
        // On récupère la connexion via le nouveau Service centralisé
        $this->pdo = DatabaseService::getInstance()->getConnection();
    }

    public function findByEmail(string $email) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- MÉTHODES ---

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
        // Attention aux injections ici si $data vient direct du $_POST sans filtre
        // Mais pour l'instant on garde ta logique pour que ça marche
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        
        $sql = "INSERT INTO utilisateurs ($columns) VALUES ($placeholders)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') return false; // Doublon
            throw $e;
        }
    }

    public function delete($id) {
        // Sécurité : on empêche de supprimer un Admin via cette méthode simple
        $stmt = $this->pdo->prepare("DELETE FROM utilisateurs WHERE id = :id AND role != 'admin'");
        return $stmt->execute([':id' => $id]);
    }
}