<?php
namespace App\Controllers;

use App\Models\Absence;
use App\Models\Etudiant;
use App\Models\Settings;                
use App\Services\NotificationService;
use App\Services\CsvImportService;      

class AbsenceController {

    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    // VUE MENSUELLE
    public function monthlyView() {
        $this->checkAuth();
        $absenceModel = new Absence();
        $etudiantModel = new Etudiant();

        $tables = $absenceModel->getAvailableMonths();

        $selectedMonth = $_GET['month'] ?? ($tables[0]['value'] ?? date('m_Y'));
        
        $monthName = "Mois inconnu";
        foreach ($tables as $t) {
            if ($t['value'] === $selectedMonth) {
                $monthName = $t['label'];
                break;
            }
        }

        $parts = explode('_', $selectedMonth);
        $m = $parts[0] ?? date('m');
        $y = $parts[1] ?? date('Y');

        // --- GESTION PAGINATION ---
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20; // 20 absences par page pour l'admin

        // 1. Calcul du total
        $totalAbsences = $absenceModel->countAllFromMonth($m, $y);
        $totalPages = ceil($totalAbsences / $perPage);

        // 2. Récupération des données paginées
        $absences = $absenceModel->getAllFromMonth($m, $y, $page, $perPage);
        // --------------------------

        foreach ($absences as &$abs) {
            $etudiant = $etudiantModel->findByCne($abs['etudiant_cne']);
            if ($etudiant) {
                $abs['nom_complet'] = strtoupper($etudiant['nom']) . ' ' . ucfirst($etudiant['prenom']);
                $abs['classe'] = $etudiant['classe'];
                $abs['email_parent'] = $etudiant['email_parent'];
                $abs['tel_parent'] = $etudiant['whatsapp_parent'] ?? $etudiant['telephone_parent'] ?? '-';
            } else {
                $abs['nom_complet'] = 'Inconnu (' . $abs['etudiant_cne'] . ')';
                $abs['classe'] = 'N/A';
                $abs['email_parent'] = '-';
            }
        }

        require_once __DIR__ . '/../Views/absences/monthly.php';
    }
    
    // EXPORT CSV (Existant)
    public function export() {
        $this->checkAuth();
        
        $month = $_GET['month'] ?? '';
        if (!$month) die("Mois manquant");

        $absenceModel = new Absence();
        list($m, $y) = explode('_', $month);
        
        $data = $absenceModel->getAllFromMonth($m, $y);
        
        $filename = 'Export_Absences_' . $month . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // BOM UTF-8
        
        fputcsv($output, ['Date', 'Heure', 'CNE', 'Matiere', 'Justifie', 'Motif'], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['date_seance'],
                $row['heure_debut'],
                $row['etudiant_cne'],
                $row['matiere'],
                $row['justifie'] ? 'Oui' : 'Non',
                $row['motif']
            ], ';');
        }
        fclose($output);
        exit;
    }

    public function notifyManual() {
        // 1. Sécurité
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { die("Accès refusé."); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idAbsence = $_POST['id'] ?? null;
            $tableName = $_POST['table'] ?? null; 

            if ($idAbsence && $tableName) {
                $pdo = \App\Services\DatabaseService::getInstance()->getConnection();

                // 2. On récupère le Numéro WHATSAPP ou TEL
                $sql = "SELECT a.id, a.date_seance, a.matiere, a.motif, a.heure_debut,
                        e.nom, e.prenom, e.cne, e.nom_parent, e.cin_parent,
                        e.whatsapp_parent, e.telephone_parent
                    FROM `$tableName` a 
                    LEFT JOIN etudiants e ON a.etudiant_cne COLLATE utf8mb4_unicode_ci = e.cne COLLATE utf8mb4_unicode_ci
                    WHERE a.id = :id";
                
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':id' => $idAbsence]);
                    $data = $stmt->fetch(\PDO::FETCH_ASSOC);

                    // 3. Choix du numéro (Whatsapp prio, sinon Tel standard)
                    $phone = !empty($data['whatsapp_parent']) ? $data['whatsapp_parent'] : ($data['telephone_parent'] ?? null);

                    if (!$data) {
                        $_SESSION['error_message'] = "❌ Absence introuvable.";
                    } 
                    elseif (empty($phone)) {
                        $_SESSION['error_message'] = "❌ Impossible : Aucun numéro de téléphone parent trouvé.";
                    }
                    else {
                        // 4. Préparation Message WhatsApp
                        $notifier = new NotificationService();
                        $parentName = !empty($data['nom_parent']) ? $data['nom_parent'] : "Parent";

                        $msg = "🔔 *Alerte EIDIA* \n\n";
                        $msg .= "Bonjour M./Mme $parentName,\n";
                        $msg .= "Votre enfant *{$data['prenom']} {$data['nom']}* a été marqué absent le : \n";
                        $msg .= "📅 " . date('d/m/Y', strtotime($data['date_seance'])) . " à " . substr($data['heure_debut'], 0, 5) . "\n";
                        $msg .= "📚 Cours : *{$data['matiere']}*\n";
                        $msg .= "📝 Motif actuel : " . ($data['motif'] ?? 'Non justifié') . "\n\n";
                        $msg .= "👉 Merci de justifier sur votre espace : " . BASE_URL . "/parent/login";

                        // 5. Envoi via Service
                        $result = $notifier->sendWhatsApp($phone, $msg);

                        if ($result['success']) {
                            // Mise à jour BDD
                            $updateSql = "UPDATE `$tableName` SET statut_notification = 'Notifié (WhatsApp)' WHERE id = :id";
                            $pdo->prepare($updateSql)->execute([':id' => $idAbsence]);
                            
                            $_SESSION['flash_message'] = "✅ WhatsApp envoyé au " . $phone;
                        } else {
                            $_SESSION['error_message'] = "❌ Erreur WhatsApp : " . ($result['message'] ?? 'Erreur inconnue');
                        }
                    }

                } catch (\PDOException $e) {
                    $_SESSION['error_message'] = "❌ Erreur SQL : " . $e->getMessage();
                }
            }
        }
        
        // Redirection
        if (isset($_SERVER['HTTP_REFERER'])) { header('Location: ' . $_SERVER['HTTP_REFERER']); } 
        else { header('Location: ' . BASE_URL . '/absences/monthly'); }
        exit;
    }

//traitement de la justif coté admin
    public function handleJustificationDecision() {
        // 1. Démarrage Session
        if (session_status() === PHP_SESSION_NONE) session_start();

        // On vérifie 'user_id'
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        // 2. Récupération des données
        $absenceId = $_POST['absence_id'] ?? null;
        $tableName = $_POST['table_name'] ?? null;
        $decision  = $_POST['decision'] ?? null;

        if (!$absenceId || !$tableName || !in_array($decision, ['VALIDE', 'REFUSE'])) {
            die("Données invalides.");
        }

        // 3. Nettoyage et Connexion DB
        $tableName = preg_replace('/[^a-z0-9_]/', '', $tableName);
        $db = \App\Services\DatabaseService::getInstance()->getConnection();

        // 4. Logique SQL
        if ($decision === 'VALIDE') {
            // Validation : On met à jour le statut, on coche "justifié", et on copie le motif
            $sql = "UPDATE `$tableName` SET 
                    justification_status = 'VALIDE',
                    justifie = 1,
                    motif = COALESCE(justification_motif, motif) 
                    WHERE id = :id";
        } else {
            // Refus : On met à jour le statut, mais ça reste injustifié (0)
            $sql = "UPDATE `$tableName` SET 
                    justification_status = 'REFUSE',
                    justifie = 0
                    WHERE id = :id";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $absenceId]);

        // 5. Redirection Intelligente
        // On extrait le mois et l'année du nom de la table (ex: absences_01_2026 -> 01_2026)
        if (preg_match('/absences_(\d{2})_(\d{4})/', $tableName, $matches)) {
            $monthParam = $matches[1] . '_' . $matches[2]; 
            // On redirige vers la vue mensuelle avec un paramètre de succès
            header("Location: " . dirname($_SERVER['SCRIPT_NAME']) . "/absences/monthly?month=$monthParam&success=1");
        } else {
            // Au cas où, retour dashboard
            header("Location: " . dirname($_SERVER['SCRIPT_NAME']) . "/dashboard");
        }
        exit;
    }
}