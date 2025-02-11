<?php

// Inclure l'autoloader Composer
require_once __DIR__ . '/vendor/autoload.php';

// Initialiser les variables d'environnement
use Config\Config;
Config::init();

// Utiliser la classe Database
use Core\Database;

try {
    // Obtenir une instance de la base de données
    $db = Database::getInstance()->getConnection();

    // Exécuter une requête simple pour tester la connexion
    $stmt = $db->query("SELECT 1");
    if ($stmt->fetchColumn()) {
        echo "Connexion réussie à la base de données.";
    }
} catch (\Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}