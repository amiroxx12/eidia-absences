<?php
// 1. FORCER L'AFFICHAGE DES ERREURS (Pour voir pourquoi ça plante)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. CHARGEMENT DES CONFIGURATIONS
// On vérifie si vendor existe, sinon on ignore (cas où composer n'est pas utilisé pour l'autoloader)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// 3. L'AUTOLOADER (INDISPENSABLE pour trouver App\Models\ParentToken)
// C'est le même code que dans ton index.php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    
    // Si la classe utilise le préfixe
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        } else {
            // Debug : Affiche si un fichier est introuvable
            echo "Fichier introuvable pour la classe : $class (Chemin cherché : $file)<br>";
        }
    }
});

use App\Models\ParentToken;

// --- CONFIGURATION DU TEST ---
$id_etudiant_cible = 5; // Assure-toi que cet ID existe dans ta table 'etudiants'
$email_fictif = "parent.test@gmail.com";
// -----------------------------

try {
    echo "<h1>🧪 Début du test...</h1>";
    
    $tokenModel = new ParentToken();
    $token = $tokenModel->create($id_etudiant_cible, $email_fictif);

    echo "<p style='color:green'>✅ Token généré avec succès pour l'étudiant ID $id_etudiant_cible.</p>";
    echo "<p>Voici le lien que le parent recevrait par email :</p>";
    
    // On construit le lien (Note: j'ai ajouté http://localhost pour être sûr)
    $link = "http://localhost/eidia-absences/public/index.php/parent/verify?token=" . $token;
    
    echo "<div style='background:#f0f9ff; padding:20px; border:1px solid #bae6fd; border-radius:8px;'>";
    echo "<a href='$link' style='font-size: 20px; font-weight: bold; color: #0284c7; text-decoration:none;'>👉 CLIQUER ICI POUR TESTER LE LIEN</a>";
    echo "</div>";
    
    echo "<br><br><hr>";
    echo "<strong>Mot de passe attendu (CIN) :</strong> AB123456";

} catch (Exception $e) {
    echo "<h2 style='color:red'>Erreur Fatale :</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<br>Trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}