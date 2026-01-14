<?php

// ⚠️ IMPORTANT : On ne définit PLUS les constantes ici (DB_HOST, etc.).
// Elles sont désormais chargées depuis le fichier .env via src/config/config.php

try {
    // On utilise directement les constantes globales qui ont été chargées par config.php
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // Création de l'objet PDO
    $pdo = new PDO($dsn, DB_USER, DB_PASS);

    // Config des erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Config des résultats par défaut
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // En prod, on évite d'afficher $e->getMessage() pour ne pas révéler d'infos sensibles
    die("Erreur critique : Impossible de se connecter à la BDD.");
}