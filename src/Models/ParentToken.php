<?php

namespace App\Models;

use App\Services\DatabaseService;
use PDO;
use Exception;

class ParentToken {
    private $conn;
    private $table = 'parent_tokens';

    public function __construct() {
        $this->conn = DatabaseService::getInstance()->getConnection();
    }


    public function create(int $etudiantId, string $emailParent): string {
        // 1. On crée une chaîne aléatoire cryptographique (64 caractères)
        $token = bin2hex(random_bytes(32));
        
        // 2. Le lien est valide 24 heures
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $sql = "INSERT INTO " . $this->table . " 
                (etudiant_id, token, email_parent, expires_at) 
                VALUES (:etudiant_id, :token, :email_parent, :expires_at)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':etudiant_id' => $etudiantId,
            ':token'       => $token,
            ':email_parent'=> $emailParent,
            ':expires_at'  => $expiresAt
        ]);

        return $token;
    }

    
    public function verify(string $token) {
        // On cherche le token qui :
        // 1. Correspond au code
        // 2. N'a pas encore été utilisé (is_used = 0)
        // 3. N'est pas expiré (expires_at > maintenant)
        
        $sql = "SELECT * FROM " . $this->table . " 
                WHERE token = :token 
                AND is_used = 0 
                AND expires_at > NOW() 
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':token' => $token]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Marque le token comme utilisé (une fois que le parent s'est connecté).
     * Comme ça, le lien ne peut pas être réutilisé indéfiniment.
     */
    public function markAsUsed(int $tokenId): void {
        $sql = "UPDATE " . $this->table . " SET is_used = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $tokenId]);
    }
    
    /**
     * Nettoyage (Optionnel) : Supprime les vieux tokens expirés pour ne pas encombrer la BDD
     */
    public function cleanExpired(): void {
        $sql = "DELETE FROM " . $this->table . " WHERE expires_at < NOW()";
        $this->conn->query($sql);
    }
}