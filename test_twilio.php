<?php
// On affiche toutes les erreurs PHP pour voir si ça plante
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Charge l'autoloader (vérifie que le dossier vendor est bien là)
require_once __DIR__ . '/vendor/autoload.php';

use Twilio\Rest\Client;

echo "<h1>Test d'envoi WhatsApp</h1>";

// Tes identifiants (Cceux que tu as donnés)
$sid    = "ACa1b89126610f8308c89e991e684b1263";
$token  = "e050bf8bf91289c774f99a295696bec4";

// Ton numéro et celui de Twilio
// IMPORTANT : Pas d'espace, et le format international
$to     = "whatsapp:+33774809667"; 
$from   = "whatsapp:+14155238886";

try {
    $twilio = new Client($sid, $token);

    echo "<p>Tentative d'envoi vers $to ...</p>";

    $message = $twilio->messages->create(
        $to,
        [
            "from" => $from,
            "body" => "Ceci est un test FINAL depuis ton script PHP. Si tu lis ça, le code fonctionne !"
            // J'ai supprimé contentSid et contentVariables qui causaient le bug
        ]
    );

    echo "<h2 style='color:green'>SUCCÈS !</h2>";
    echo "Message envoyé. SID : " . $message->sid;
    echo "<br>Vérifie ton téléphone maintenant.";

} catch (Exception $e) {
    echo "<h2 style='color:red'>ERREUR :</h2>";
    echo "<pre>" . print_r($e->getMessage(), true) . "</pre>";
}