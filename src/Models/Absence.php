<?php
namespace App\Models;

use App\Services\DatabaseService;
use PDO;
use PDOException;

class Absence {
    private $conn;
    private $currentTable; 

    public function __construct() {
        $this->conn = DatabaseService::getInstance()->getConnection();
        $this->setMonth(date('Y-m-d'));
    }

    public function setMonth($dateString) {
        $dateString = str_replace('/', '-', $dateString);
        $timestamp = strtotime($dateString);
        if (!$timestamp) $timestamp = time(); 
        
        $suffix = date('m_Y', $timestamp); 
        $this->currentTable = "absences_" . $suffix;
    }

    public function ensureTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->currentTable . "` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `etudiant_cne` VARCHAR(20) NOT NULL,
            `date_seance` DATE NOT NULL,
            `heure_debut` TIME NOT NULL,
            `matiere` VARCHAR(100) NOT NULL,
            `justifie` TINYINT(1) DEFAULT 0,
            `motif` TEXT DEFAULT NULL,
            `statut_notification` ENUM('non_notifie', 'notifie', 'echec') DEFAULT 'non_notifie',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_absence` (`etudiant_cne`, `date_seance`, `heure_debut`),
            KEY `idx_cne` (`etudiant_cne`),
            KEY `idx_date` (`date_seance`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $this->conn->exec($sql);
            return true;
        } catch (PDOException $e) {
            die("Erreur création table mensuelle : " . $e->getMessage());
        }
    }

    public function create(array $data) {
        if (!empty($data['date_seance'])) {
            $this->setMonth($data['date_seance']);
            $this->ensureTableExists(); 
        }
        if (empty($data['etudiant_cne']) || empty($data['date_seance'])) return false;

        $dateFormatted = date('Y-m-d', strtotime(str_replace('/', '-', $data['date_seance'])));
        $query = "INSERT INTO `" . $this->currentTable . "` 
                  (etudiant_cne, date_seance, heure_debut, matiere, justifie, motif) 
                  VALUES (:cne, :date_seance, :heure_debut, :matiere, 0, :motif)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':cne', htmlspecialchars(strip_tags($data['etudiant_cne'])));
        $stmt->bindValue(':date_seance', $dateFormatted);
        $stmt->bindValue(':heure_debut', $data['heure_debut'] ?? '00:00:00');
        $stmt->bindValue(':matiere', $data['matiere'] ?? 'Non défini');
        $stmt->bindValue(':motif', $data['motif'] ?? '');

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            return ($e->getCode() == '23000') ? null : false;
        }
    }

    public function getAllFromMonth($month, $year) {
        $tableName = "absences_" . sprintf("%02d", $month) . "_" . $year;
        try {
            $stmt = $this->conn->query("SELECT * FROM `$tableName` ORDER BY date_seance DESC, heure_debut ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return []; 
        }
    }

    public function countByMonth($cne) {
        // On s'assure que la table existe avant de compter (sinon erreur SQL)
        if (!$this->tableExists($this->currentTable)) return 0;

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM `" . $this->currentTable . "` WHERE etudiant_cne = :cne");
        $stmt->execute([':cne' => $cne]);
        return (int)$stmt->fetchColumn();
    }

    // 2. Récupère les 5 dernières absences du mois pour faire la liste
    public function getLastFiveByMonth($cne) {
        if (!$this->tableExists($this->currentTable)) return [];

        $stmt = $this->conn->prepare("SELECT * FROM `" . $this->currentTable . "` 
                                      WHERE etudiant_cne = :cne 
                                      ORDER BY date_seance DESC, heure_debut DESC 
                                      LIMIT 5");
        $stmt->execute([':cne' => $cne]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Utilitaire pour éviter de planter si la table n'existe pas encore
    private function tableExists($tableName) {
        try {
            $result = $this->conn->query("SELECT 1 FROM `$tableName` LIMIT 1");
            return $result !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAvailableMonths() {
        $stmt = $this->conn->query("SHOW TABLES LIKE 'absences_%'");
        $rawTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $months = [];
        foreach ($rawTables as $t) {
            if (preg_match('/absences_(\d{2})_(\d{4})/', $t, $matches)) {
                $months[] = [
                    'value' => $matches[1] . '_' . $matches[2],
                    'label' => $this->getMonthLabel($matches[1]) . ' ' . $matches[2]
                ];
            }
        }
        usort($months, function($a, $b) { return strcmp($b['value'], $a['value']); });
        return $months;
    }

    /**
     * Méthode manquante qui causait l'erreur
     */
    private function getMonthLabel($num) {
        $names = [
            '01'=>'Janvier', '02'=>'Février', '03'=>'Mars', '04'=>'Avril', 
            '05'=>'Mai', '06'=>'Juin', '07'=>'Juillet', '08'=>'Août', 
            '09'=>'Septembre', '10'=>'Octobre', '11'=>'Novembre', '12'=>'Décembre'
        ];
        return $names[$num] ?? $num;
    }

    public function countAllGlobal() {
        $stmt = $this->conn->query("SHOW TABLES LIKE 'absences_%'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $total = 0;
        foreach ($tables as $table) {
            if (preg_match('/absences_\d{2}_\d{4}/', $table)) {
                $total += $this->conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            }
        }
        return $total;
    }

    public function countTotalByStudent($cne) {
        $stmt = $this->conn->query("SHOW TABLES LIKE 'absences_%'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $total = 0;
        foreach ($tables as $table) {
            if (preg_match('/absences_\d{2}_\d{4}/', $table)) {
                $stmt_count = $this->conn->prepare("SELECT COUNT(*) FROM `$table` WHERE etudiant_cne = :cne");
                $stmt_count->execute([':cne' => $cne]);
                $total += $stmt_count->fetchColumn();
            }
        }
        return $total;
    }

    public function getByEtudiantGlobal($cne) {
        $stmt = $this->conn->query("SHOW TABLES LIKE 'absences_%'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $allAbsences = [];
        foreach ($tables as $table) {
            if (preg_match('/absences_\d{2}_\d{4}/', $table)) {
                $stmt_get = $this->conn->prepare("SELECT *, '$table' as source_table FROM `$table` WHERE etudiant_cne = :cne");
                $stmt_get->execute([':cne' => $cne]);
                $allAbsences = array_merge($allAbsences, $stmt_get->fetchAll(PDO::FETCH_ASSOC));
            }
        }
        usort($allAbsences, function($a, $b) { return strtotime($b['date_seance']) - strtotime($a['date_seance']); });
        return $allAbsences;
    }

    public function countAbsencesThisMonth($studentId) {
    // Exemple SQL (adapte selon tes noms de colonnes)
    $sql = "SELECT COUNT(*) as total FROM absences 
            WHERE student_id = :id 
            AND MONTH(date) = MONTH(CURRENT_DATE()) 
            AND YEAR(date) = YEAR(CURRENT_DATE())";
            
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $studentId]);
    
    return $stmt->fetchColumn(); // Retourne juste le chiffre (ex: 3)
}
}