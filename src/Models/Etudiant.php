<?php
namespace App\Models;

use App\Services\DatabaseService;
use PDO;
use PDOException;

class Etudiant {
    private $pdo;

    public function __construct() {
        // On garde ta connexion existante (Singleton)
        $this->pdo = DatabaseService::getInstance()->getConnection();
    }

    /**
     * Créer ou mettre à jour (Import CSV)
     * Compatible avec CsvImportService
     * @param array $data
     * @return bool|null (True = Créé/Mis à jour, False = Erreur)
     */
    public function create(array $data) {
        // 1. Sécurité : Le CNE est obligatoire
        if (empty($data['cne'])) {
            return false;
        }

        $allowedColumns = [
            'cne', 'nom', 'prenom', 'email', 'telephone', 'classe',
            'nom_parent', 'email_parent', 'telephone_parent', 'whatsapp_parent', 'adresse'
        ];

        // 2. Nettoyage : On ne garde que les colonnes autorisées
        $cleanData = array_intersect_key($data, array_flip($allowedColumns));

        // Préparation dynamique de la requête
        $columns = implode(", ", array_keys($cleanData));
        $placeholders = ":" . implode(", :", array_keys($cleanData));
        
        $updates = [];
        foreach ($cleanData as $key => $value) {
            // On met à jour toutes les infos sauf le CNE (qui est la clé primaire/unique)
            if ($key !== 'cne') {
                $updates[] = "$key = VALUES($key)";
            }
        }
        $updateString = implode(", ", $updates);

        // LOGIQUE : Si le CNE existe, on met à jour les infos (téléphone, adresse...)
        // C'est mieux que de rejeter le doublon pour la gestion des étudiants.
        $sql = "INSERT INTO etudiants ($columns) VALUES ($placeholders) 
                ON DUPLICATE KEY UPDATE $updateString";

        try {
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($cleanData as $key => $value) {
                // Gestion propre des NULL
                $val = ($value === '' || $value === null) ? null : $value;
                $stmt->bindValue(":$key", $val);
            }
            
            return $stmt->execute(); // Retourne TRUE si inséré ou mis à jour
            
        } catch (PDOException $e) {
            // Log l'erreur pour le debug admin
            error_log("Erreur Import Etudiant (CNE: " . $data['cne'] . ") : " . $e->getMessage());
            return false;
        }
    }

    // --- Les autres méthodes restent inchangées (Lecture) ---

    // Récupérer la liste avec FILTRES (Recherche + Classe)
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

    // Récupérer un seul étudiant
    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM etudiants WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Supprimer un étudiant
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM etudiants WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Mettre à jour (Édition manuelle)
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

    // Listes des classes pour le menu déroulant
    public function getDistinctClasses() {
        $stmt = $this->pdo->query("SELECT DISTINCT classe FROM etudiants WHERE classe IS NOT NULL AND classe != '' ORDER BY classe ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Stats Dashboard
    public function countAll() {
        return $this->pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
    }
    public function countClasses() {
        return $this->pdo->query("SELECT COUNT(DISTINCT classe) FROM etudiants")->fetchColumn();
    }

    /**
     * Récupère la liste des étudiants AVEC le compteur d'absences
     * Utilise un LEFT JOIN pour inclure même ceux qui ont 0 absence
     */
    public function findAllWithStats($filters = []) {
        $sql = "SELECT e.*, COUNT(a.id) as total_absences 
                FROM etudiants e 
                LEFT JOIN absences a ON e.cne = a.etudiant_cne 
                WHERE 1=1";
        
        $params = [];

        // Filtre par classe
        if (!empty($filters['classe'])) {
            $sql .= " AND e.classe = :classe";
            $params[':classe'] = $filters['classe'];
        }

        // Filtre recherche (Nom ou CNE)
        if (!empty($filters['search'])) {
            $sql .= " AND (e.nom LIKE :search OR e.prenom LIKE :search OR e.cne LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // On groupe par étudiant pour que le COUNT fonctionne
        $sql .= " GROUP BY e.cne";
        
        // Tri : Les cancres en premier (ceux qui ont le plus d'absences)
        $sql .= " ORDER BY total_absences DESC, e.nom ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByCne($cne) {
        $stmt = $this->pdo->prepare("SELECT * FROM etudiants WHERE cne = :cne");
        $stmt->bindParam(':cne', $cne);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}