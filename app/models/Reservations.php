<?php

namespace Models;

use PDO;

class Reservations {
    private $db;

    // Constructeur pour initialiser la connexion à la base de données
    public function __construct(PDO $db) {
        $this->db = $db;
    }

}