<?php
namespace App\Models;

use App\Services\DatabaseService;
use App\Services\AuditService;
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
        $timestamp = strtotime(str_replace('/', '-', $dateString));
        if (!$timestamp) $timestamp = time(); 
        $this->currentTable = "absences_" . date('m_Y', $timestamp);
    }
    
    public function ensureTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->currentTable . "` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `etudiant_cne` VARCHAR(50) NOT NULL,
            `date_seance` DATE NOT NULL,
            `heure_debut` TIME NOT NULL,
            `matiere` VARCHAR(100) NOT NULL,
            
            `justifie` TINYINT(1) DEFAULT 0,
            `motif` TEXT DEFAULT NULL,
            `statut_notification` VARCHAR(50) DEFAULT 'Non notifié',
            
            `justification_status` ENUM('NON_JUSTIFIE', 'EN_ATTENTE', 'VALIDE', 'REFUSE') DEFAULT 'NON_JUSTIFIE',
            `justification_motif` TEXT DEFAULT NULL,
            `justification_file` VARCHAR(255) DEFAULT NULL,
            `justification_date` DATETIME DEFAULT NULL,

            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_absence` (`etudiant_cne`, `date_seance`, `heure_debut`),
            KEY `idx_cne` (`etudiant_cne`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $this->conn->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur Table Absence: " . $e->getMessage());
            return false;
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
        $stmt->bindValue(':cne', $data['etudiant_cne']);
        $stmt->bindValue(':date_seance', $dateFormatted);
        $stmt->bindValue(':heure_debut', $data['heure_debut'] ?? '00:00:00');
        $stmt->bindValue(':matiere', $data['matiere'] ?? 'Non défini');
        $stmt->bindValue(':motif', $data['motif'] ?? '');

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAllFromMonth($month, $year, $page = 1, $perPage = 10) {
        $m = sprintf("%02d", (int)$month);
        $y = (int)$year;
        $tableName = "absences_{$m}_{$y}";
        
        // Calcul du point de départ (Offset)
        $offset = ($page - 1) * $perPage;
        
        try {
            // Vérif si table existe
            $check = $this->conn->query("SHOW TABLES LIKE '$tableName'");
            if($check->rowCount() == 0) return [];

            // On ajoute LIMIT et OFFSET
            $sql = "SELECT * FROM `$tableName` ORDER BY date_seance DESC, heure_debut ASC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            
            // Important : bindValue avec TYPE INT pour que le SQL comprenne
            $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return []; 
        }
    }

    // AJOUTE CETTE FONCTION JUSTE EN DESSOUS (Pour compter le total de pages)
    public function countAllFromMonth($month, $year) {
        $m = sprintf("%02d", (int)$month);
        $y = (int)$year;
        $tableName = "absences_{$m}_{$y}";
        try {
            $check = $this->conn->query("SHOW TABLES LIKE '$tableName'");
            if($check->rowCount() == 0) return 0;
            return (int)$this->conn->query("SELECT COUNT(*) FROM `$tableName`")->fetchColumn();
        } catch (PDOException $e) { return 0; }
    }

    public function countByMonth($cne) {
        if (!$this->tableExists($this->currentTable)) return 0;
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM `" . $this->currentTable . "` WHERE etudiant_cne = :cne");
        $stmt->execute([':cne' => $cne]);
        return (int)$stmt->fetchColumn();
    }

    // --- 👇 MÉTHODES GLOBALES (MANQUANTES) POUR LE DASHBOARD ---

    public function countTotalByStudent($cne) {
        $stmt = $this->conn->query("SHOW TABLES LIKE 'absences_%'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $total = 0;
        foreach ($tables as $table) {
            // Vérification format pour sécurité
            if (preg_match('/absences_\d{2}_\d{4}/', $table)) {
                $stmt_count = $this->conn->prepare("SELECT COUNT(*) FROM `$table` WHERE etudiant_cne = :cne");
                $stmt_count->execute([':cne' => $cne]);
                $total += (int)$stmt_count->fetchColumn();
            }
        }
        return $total;
    }

    // Modifiée pour la pagination Parent
    public function getByEtudiantGlobal($cne, $page = 1, $perPage = 5) {
        $stmt = $this->conn->query("SHOW TABLES LIKE 'absences_%'");
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $allAbsences = [];

        foreach ($tables as $table) {
            if (preg_match('/absences_\d{2}_\d{4}/', $table)) {
                $stmt_get = $this->conn->prepare("SELECT *, '$table' as source_table FROM `$table` WHERE etudiant_cne = :cne");
                $stmt_get->execute([':cne' => $cne]);
                $rows = $stmt_get->fetchAll(\PDO::FETCH_ASSOC);
                if($rows) {
                    $allAbsences = array_merge($allAbsences, $rows);
                }
            }
        }

        // Tri par date décroissante
        usort($allAbsences, function($a, $b) { 
            return strtotime($b['date_seance']) - strtotime($a['date_seance']); 
        });

        // --- PAGINATION PHP (Découpage du tableau) ---
        $total = count($allAbsences); // On compte tout
        $offset = ($page - 1) * $perPage; // On calcule le point de départ
        
        // On ne garde que la tranche demandée
        $data = array_slice($allAbsences, $offset, $perPage);

        // On retourne un tableau complet
        return [
            'data'  => $data,  // Les absences de la page
            'total' => $total  // Le nombre total pour calculer les pages
        ];
    }


    public function countAllGlobal() {
        $stmt = $this->conn->query("SHOW TABLES LIKE 'absences_%'");
        $total = 0;
        foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $table) {
            if (preg_match('/absences_\d{2}_\d{4}/', $table)) {
                $total += (int)$this->conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            }
        }
        return $total;
    }

    // -----------------------------------------------------------

    public function notifyManual() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { die("Accès refusé."); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $rawTable = $_POST['table'] ?? null; 
            $table = preg_replace('/[^a-z0-9_]/', '', $rawTable); 

            if ($id && $table) {
                $pdo = \App\Services\DatabaseService::getInstance()->getConnection();
                
                // Récupération des infos
                $sql = "SELECT a.*, e.nom, e.prenom, e.email_parent, e.whatsapp_parent, e.telephone_parent 
                        FROM `$table` a 
                        LEFT JOIN etudiants e ON a.etudiant_cne = e.cne 
                        WHERE a.id = :id";     
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $id]);
                $data = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($data) {
                    $notifier = new \App\Services\NotificationService();
                    $actions = [];
                    $logDetails = []; // Notre journal de bord

                    // 1. GESTION WHATSAPP
                    $phone = $data['whatsapp_parent'] ?? $data['telephone_parent'];
                    
                    if (!empty($phone)) {
                        // On a un numéro, on tente l'envoi
                        $msg = "⚠️ Absence: {$data['prenom']} {$data['nom']} le {$data['date_seance']}.";
                        $resWa = $notifier->sendWhatsApp($phone, $msg);
                        
                        if($resWa['success']) {
                            $actions[] = "WhatsApp";
                            $logDetails[] = "WhatsApp: OK";
                        } else {
                            $logDetails[] = "WhatsApp: ÉCHEC (" . ($resWa['message'] ?? 'Erreur inconnue') . ")";
                        }
                    } else {
                        // Pas de numéro trouvé
                        $logDetails[] = "WhatsApp: Ignoré (Pas de numéro)";
                    }

                    // 2. GESTION EMAIL
                    if (!empty($data['email_parent'])) {
                         $subject = "Absence : " . $data['nom'];
                         $body = "Bonjour,\nVotre enfant a été absent le " . $data['date_seance'];
                         $resEmail = $notifier->sendEmail($data['email_parent'], $subject, $body);
                         
                         if($resEmail['success']) {
                             $actions[] = "Email";
                             $logDetails[] = "Email: OK";
                         } else {
                             $logDetails[] = "Email: ÉCHEC";
                         }
                    } else {
                        $logDetails[] = "Email: Ignoré (Pas d'email)";
                    }

                    // 3. ENREGISTREMENT DU LOG
                    // On construit le message final
                    $auditMessage = "Notif Manuelle pour {$data['nom']} {$data['prenom']} -> " . implode(' | ', $logDetails);
                    
                    if (!empty($actions)) {
                        // SUCCÈS (Au moins un canal a marché)
                        $pdo->prepare("UPDATE `$table` SET statut_notification = 'Notifié' WHERE id = :id")->execute([':id' => $id]);
                        
                        AuditService::log('NOTIFICATION', $auditMessage); // Log Vert
                        $_SESSION['flash_message'] = "✅ Succès : " . implode(' + ', $actions);
                    } else {
                        // ÉCHEC TOTAL (Aucun canal n'a marché ou pas de coordonnées)
                        AuditService::log('NOTIFICATION_FAIL', $auditMessage); // Log Rouge
                        $_SESSION['error_message'] = "❌ Aucune notification envoyée (voir logs).";
                    }
                }
            }
        }
        if (isset($_SERVER['HTTP_REFERER'])) header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Helpers
    private function tableExists($tableName) {
        try {
            $stmt = $this->conn->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
            $stmt->execute([$tableName]);
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) { return false; }
    }
    
    public function getAvailableMonths() {
        $stmt = $this->conn->query("SHOW TABLES LIKE 'absences_%'");
        $rawTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $months = [];
        foreach ($rawTables as $t) {
            if (preg_match('/absences_(\d{2})_(\d{4})/', $t, $matches)) {
                $months[] = ['value' => $matches[1] . '_' . $matches[2], 'label' => $matches[1] . '/' . $matches[2]];
            }
        }
        return array_reverse($months);
    }
}