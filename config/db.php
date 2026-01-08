<?php

// parametres de connexion (localement) 

define('DB_HOST', 'localhost');
define('DB_NAME', 'eidia_absences');
define('DB_USER','root');
define('DB_PASS', '');


try {
    //creation de l'opjet PDO (PHP Data Object) a lightweight, consistent database access abstraction layer that provides a uniform interface for interacting with various databases in PHP
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",DB_USER, DB_PASS);

    //config des erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //config des resultats
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // si la connexion échoue, on arrête tout et affiche la raison
    die("Erreur critique : Impossible de se connecter à la BDD. <br>".$e->getMessage());
}