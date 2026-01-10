<?php
namespace App\Controllers;

use App\Models\Settings;

class SettingsController {

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Seul l'ADMIN accède à la config technique
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = "Accès refusé. Seul l'administrateur peut modifier la configuration.";
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    public function index() {
        $this->checkAdmin();
        $model = new Settings();
        
        $config = $model->getAll();
        $templates = $model->getTemplates();

        require_once __DIR__ . '/../Views/admin/settings.php';
    }

    public function save() {
        $this->checkAdmin();
        $model = new Settings();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Sauvegarde Config Technique
            if (isset($_POST['config_type'])) {
                foreach ($_POST as $key => $value) {
                    if ($key !== 'config_type') {
                        $model->update($key, trim($value));
                    }
                }
                $_SESSION['flash_message'] = "Configuration technique mise à jour.";
            }

            // Sauvegarde Templates
            if (isset($_POST['template_id'])) {
                $model->updateTemplate(
                    $_POST['template_id'], 
                    $_POST['subject'] ?? null, 
                    $_POST['body']
                );
                $_SESSION['flash_message'] = "Modèle de message mis à jour.";
            }
        }

        header('Location: ' . BASE_URL . '/settings');
        exit;
    }

    public function test() {
        $this->checkAdmin(); // Sécurité : seul l'admin peut tester
        
        // On instancie le service qu'on vient de corriger
        // (Assure-toi que le 'use App\Services\NotificationService;' est bien en haut du fichier)
        $notifier = new \App\Services\NotificationService();

        $channel = $_GET['channel'] ?? '';
        $result = ['success' => false, 'message' => 'Canal non spécifié'];

        // ===========================
        // CAS 1 : TEST EMAIL
        // ===========================
        if ($channel === 'email') {
            // On prend l'email de la session admin, ou un email par défaut
            $testEmail = $_SESSION['user_email'] ?? 'amirox.ouafi@gmail.com'; 
            
            $subject = "Test SMTP [EIDIA]";
            $body = "Ceci est un email de test envoyé depuis le panneau d'administration.";
            
            $result = $notifier->sendEmail($testEmail, $subject, $body);
        }

        // ===========================
        // CAS 2 : TEST WHATSAPP
        // ===========================
        elseif ($channel === 'whatsapp') {
            // On récupère le numéro passé dans l'URL (ex: &phone=2126...)
            $phone = $_GET['phone'] ?? null;
            
            if ($phone) {
                $message = "🔔 Test Admin : Le système de notification WhatsApp fonctionne correctement !";
                $result = $notifier->sendWhatsApp($phone, $message);
            } else {
                $result = ['success' => false, 'message' => 'Numéro de téléphone manquant pour le test.'];
            }
        }

        // ===========================
        // RETOUR UTILISATEUR
        // ===========================
        if ($result['success']) {
            $_SESSION['flash_message'] = "✅ " . $result['message'];
        } else {
            $_SESSION['error_message'] = "❌ " . $result['message'];
        }
        
        // On recharge la page (reste sur l'onglet actif idéalement)
        header('Location: ' . BASE_URL . '/settings');
        exit;
    }
}