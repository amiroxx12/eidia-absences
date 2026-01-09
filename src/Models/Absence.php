<?php
namespace App\Models;

use App\Services\DatabaseService; // <-- On utilise le Service, pas la Config directe
use PDO;
use PDOException;

class Absence {
    private $conn;
    private $table = 'absences';

    public function __construct() {
        // CORRECTION : On récupère l'instance unique de la connexion
        // Au lieu de faire 'new Database()', on appelle le Singleton
        $this->conn = DatabaseService::getInstance()->getConnection();
    }

    /**
     * Crée une absence (Phase 3 - Import)
     */
    public function create(array $data) {
        if (empty($data['etudiant_cne']) || empty($data['date_seance'])) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . " 
                  (etudiant_cne, date_seance, heure_debut, matiere, justifie) 
                  VALUES 
                  (:cne, :date_seance, :heure_debut, :matiere, 0)";

        $stmt = $this->conn->prepare($query);

        // Nettoyage et valeurs par défaut
        $cne = htmlspecialchars(strip_tags($data['etudiant_cne']));
        $date = htmlspecialchars(strip_tags($data['date_seance']));
        $heure = htmlspecialchars(strip_tags($data['heure_debut'] ?? '00:00:00'));
        $matiere = htmlspecialchars(strip_tags($data['matiere'] ?? 'Non défini'));

        $stmt->bindParam(':cne', $cne);
        $stmt->bindParam(':date_seance', $date);
        $stmt->bindParam(':heure_debut', $heure);
        $stmt->bindParam(':matiere', $matiere);

        try {
            if($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            // Code 23000 = Doublon (Unique Key violation)
            if ($e->getCode() == '23000') {
                return null; 
            }
            error_log("Erreur Insertion Absence : " . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * Compte TOTAL des absences (Pour le Dashboard)
     */
    public function countAllGlobal() {
        $query = "SELECT COUNT(*) FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Récupère les absences d'un étudiant
     */
    public function getByEtudiant($cne) {
        $query = "SELECT * FROM " . $this->table . " WHERE etudiant_cne = :cne ORDER BY date_seance DESC, heure_debut DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cne', $cne);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte les absences d'un étudiant
     */
    public function countTotal($cne) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE etudiant_cne = :cne";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cne', $cne);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}