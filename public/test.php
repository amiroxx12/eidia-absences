<?php
// public/test.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

echo "<h1>Test de Configuration</h1>";

// 1. Test de l'URL
echo "<strong>URL du site (BASE_URL) :</strong> " . BASE_URL . "<br><br>";

// 2. Test de la BDD
if (isset($pdo)) {
    echo "<strong>Connexion BDD :</strong> <span style='color:green'>✅ SUCCÈS (L'objet PDO existe)</span><br>";
    
    // On tente une vraie requête pour être sûr à 100%
    $stmt = $pdo->query("SELECT count(*) as total FROM utilisateurs");
    $res = $stmt->fetch();
    echo "<strong>Nombre d'utilisateurs :</strong> " . $res['total'];
} else {
    echo "<strong>Connexion BDD :</strong> <span style='color:red'>❌ ÉCHEC</span>";
}