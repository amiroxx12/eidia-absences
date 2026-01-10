<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\Settings;
use Twilio\Rest\Client; // <--- IMPERATIF : Import du Client Twilio

class NotificationService {
    
    private $settings; // On garde ce nom partout

    public function __construct() {
        $model = new Settings();
        $this->settings = $model->getAll(); 
    }

    // =========================================================
    // PARTIE 1 : EMAIL
    // =========================================================
    public function sendEmail($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $this->settings['smtp_host'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->settings['smtp_user'] ?? '';
            $mail->Password   = $this->settings['smtp_pass'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = $this->settings['smtp_port'] ?? 587;
            $mail->CharSet    = 'UTF-8';

            $fromEmail = $this->settings['smtp_from_email'] ?? $this->settings['smtp_user']; // Petit fix ici pour matcher le form
            $fromName  = $this->settings['smtp_from_name'] ?? 'EIDIA Admin';
            
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = nl2br($body);
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return ['success' => true, 'message' => 'Email envoyé avec succès.'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => "Erreur d'envoi : {$mail->ErrorInfo}"];
        }
    }

    // =========================================================
    // PARTIE 2 : WHATSAPP (Via Twilio)
    // =========================================================
    public function sendWhatsApp($toNumber, $messageBody) {
        
        // 1. Correction de la variable : $this->settings et pas $this->config
        if (empty($this->settings['twilio_sid']) || empty($this->settings['twilio_token'])) {
            return ['success' => false, 'message' => 'Configuration Twilio manquante.'];
        }

        // 2. Utilisation de ta fonction utilitaire pour formater correctement
        // Ça gère automatiquement les 06... -> +2126...
        $to = $this->formatNumberForTwilio($toNumber);

        // Correction variable settings ici aussi
        $from = $this->settings['twilio_from']; 
        if (strpos($from, 'whatsapp:') === false) {
            $from = "whatsapp:" . $from;
        }

        try {
            // 3. Instanciation (Le namespace est importé en haut maintenant)
            $twilio = new Client($this->settings['twilio_sid'], $this->settings['twilio_token']);

            $message = $twilio->messages->create(
                $to, 
                [
                    "from" => $from,
                    "body" => $messageBody
                ]
            );

            return ['success' => true, 'message' => 'WhatsApp envoyé ! SID: ' . $message->sid];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur Twilio : ' . $e->getMessage()];
        }
    }

    // =========================================================
    // UTILITAIRES
    // =========================================================
    private function formatNumberForTwilio($number) {
        // 1. On enlève tout ce qui n'est pas chiffre ou +
        $clean = preg_replace('/[^0-9+]/', '', $number);
        
        // 2. Gestion spécifique Maroc (06... ou 07... -> +212...)
        if (strpos($clean, '0') === 0) {
            // On enlève le 0 et on met +212
            $clean = '+212' . substr($clean, 1);
        }
        
        // 3. Twilio a besoin du préfixe "whatsapp:"
        if (strpos($number, 'whatsapp:') === false) {
            return 'whatsapp:' . $clean;
        }
        
        return $clean; // Si "whatsapp:" était déjà là
    }
}