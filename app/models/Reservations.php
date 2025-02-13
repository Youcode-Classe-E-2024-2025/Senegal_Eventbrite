<?php
namespace Models;
use PDO;

class Reservations {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Ajoute une nouvelle réservation
     */
    public function addReservation($userId, $eventId, $ticketType, $quantity, $totalPrice, $fullName, $email, $uniqueId) {
        try {
            // Vérifier si le type de ticket est valide
            if (!in_array($ticketType, ['VIP', 'PREMIUM', 'STANDART'])) {
                throw new \Exception("Type de ticket invalide : " . $ticketType);
            }
    
            // Log des données avant l'insertion
            error_log("Ajout de réservation avec les paramètres : " . print_r([
                'user_id' => $userId,
                'event_id' => $eventId,
                'ticket_type' => $ticketType,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'full_name' => $fullName,
                'email' => $email,
                'unique_id' => $uniqueId
            ], true));
    
            // Préparer la requête d'insertion avec les nouveaux champs
            $sql = "INSERT INTO reservations (user_id, event_id, ticket_type, quantity, total_price, status, unique_id, full_name, email, reservation_date, created_at, updated_at) 
                    VALUES (:user_id, :event_id, :ticket_type, :quantity, :total_price, :status, :unique_id, :full_name, :email, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            
            // Préparer la déclaration SQL
            $stmt = $this->db->prepare($sql);
    
            // Exécuter la requête avec les paramètres appropriés
            $stmt->execute([
                ':user_id' => $userId,
                ':event_id' => $eventId,
                ':ticket_type' => $ticketType,
                ':quantity' => $quantity,
                ':total_price' => $totalPrice,
                ':status' => 'pending', 
                ':unique_id' => $uniqueId,
                ':full_name' => $fullName,
                ':email' => $email
            ]);
    
            // Récupérer l'ID de la réservation nouvellement insérée
            $reservationId = $this->db->lastInsertId();
    
            // Mettre à jour les statistiques de l'événement
            $this->updateEventStatistics($eventId, $quantity);
    
            return $reservationId;
    
        } catch (\PDOException $e) {
            error_log('Erreur lors de l\'ajout de la réservation: ' . $e->getMessage());
            throw new \Exception("Erreur lors de l'ajout de la réservation: " . $e->getMessage());
        }
    }
    

    /**
     * Met à jour les statistiques de l'événement après une réservation
     */
    private function updateEventStatistics($eventId, $quantity) {
        try {
            $sql = "INSERT INTO event_statistics (event_id, tickets_sold, participants_count) 
                    VALUES (:event_id, :quantity, :quantity)
                    ON CONFLICT (event_id) 
                    DO UPDATE SET 
                        tickets_sold = event_statistics.tickets_sold + :quantity,
                        participants_count = event_statistics.participants_count + :quantity";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':event_id' => $eventId,
                ':quantity' => $quantity
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur lors de la mise à jour des statistiques: ' . $e->getMessage());
            // On ne throw pas l'erreur ici pour ne pas bloquer la réservation
        }
    }

    /**
     * Récupère toutes les réservations d'un utilisateur
     */
    public function getUserReservations($userId) {
        try {
            $sql = "SELECT r.*, e.title as event_title, e.date_start, e.location 
                    FROM reservations r 
                    JOIN events e ON r.event_id = e.id 
                    WHERE r.user_id = :user_id 
                    ORDER BY r.reservation_date DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des réservations: " . $e->getMessage());
        }
    }

    /**
     * Vérifie la disponibilité des places pour un événement
     */
    public function checkEventAvailability($eventId, $quantity) {
        try {
            $sql = "SELECT e.capacity, COALESCE(SUM(r.quantity), 0) as reserved
                    FROM events e
                    LEFT JOIN reservations r ON e.id = r.event_id
                    WHERE e.id = :event_id
                    GROUP BY e.capacity";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':event_id' => $eventId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                throw new \Exception("Événement non trouvé");
            }
            
            return ($result['capacity'] - $result['reserved']) >= $quantity;
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la vérification de la disponibilité: " . $e->getMessage());
        }
    }
}