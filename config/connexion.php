<?php
    require 'config.php';
    try {    
        // connection string
        $dsn="mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;

        // Connecting to database
        $pdo = new PDO($dsn, DB_USER, DB_PASS);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e){
        // Gestion de l'erreur 
        die('Erreur de connexion: ' . $e->getMessage());
    }
?>