<?php
 

namespace Models ;

class Reservations {
    private $db;

    // Constructeur pour initialiser la connexion à la base de données
    public function __construct($db) {
        $this->db = $db;
    }

    // Ajouter une nouvelle réservation
    public function addReservation($user_id, $event_id, $ticket_type, $quantity, $total_price, $qr_code, $status) {
        $query = "INSERT INTO reservations (user_id, event_id, ticket_type, quantity, total_price, qr_code, status)
                  VALUES (:user_id, :event_id, :ticket_type, :quantity, :total_price, :qr_code, :status)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':event_id', $event_id);
        $stmt->bindParam(':ticket_type', $ticket_type);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':total_price', $total_price);
        $stmt->bindParam(':qr_code', $qr_code);
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }

    // Mettre à jour une réservation existante
    public function updateReservation($id, $ticket_type, $quantity, $total_price, $qr_code, $status) {
        $query = "UPDATE reservations 
                  SET ticket_type = :ticket_type, 
                      quantity = :quantity, 
                      total_price = :total_price, 
                      qr_code = :qr_code, 
                      status = :status 
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':ticket_type', $ticket_type);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':total_price', $total_price);
        $stmt->bindParam(':qr_code', $qr_code);
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }

    // Supprimer une réservation par son ID
    public function deleteReservation($id) {
        $query = "DELETE FROM reservations WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    // Récupérer toutes les réservations
    public function getAllReservations() {
        $query = "SELECT * FROM reservations";
        $stmt = $this->db->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer une réservation par son ID
    public function getReservationById($id) {
        $query = "SELECT * FROM reservations WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupérer les réservations d'un utilisateur spécifique
    public function getUserReservations($user_id) {
        $query = "SELECT * FROM reservations WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les réservations pour un événement spécifique
    public function getEventReservations($event_id) {
        $query = "SELECT * FROM reservations WHERE event_id = :event_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':event_id', $event_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}