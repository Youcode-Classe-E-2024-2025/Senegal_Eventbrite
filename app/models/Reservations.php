<?php
namespace Models;
use PDO;

class Reservations {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function addReservation($userId, $eventId, $ticketType, $quantity, $totalPrice, $fullName, $email, $vipOptions = []) {
        try {
            // Récupérer l'ID du ticket depuis son type
            $ticketId = $this->getTicketIdByType($ticketType);
    
            // Vérifier si le ticket existe
            if (!$ticketId) {
                throw new \Exception("Type de ticket invalide : " . $ticketType);
            }
    
            // Log des données avant l'insertion
            error_log("Ajout de réservation avec les paramètres : " . print_r([
                'user_id' => $userId,
                'event_id' => $eventId,
                'ticket_id' => $ticketId, // Maintenant c'est un entier
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'full_name' => $fullName,
                'email' => $email,
                'vip_options' => $vipOptions
            ], true));
            error_log("Exécution de la requête : " . $sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':event_id' => $eventId,
                ':ticket_id' => $ticketId,
                ':quantity' => $quantity,
                ':total_price' => $totalPrice,
                ':status' => 'reserved',
                ':full_name' => $fullName,
                ':email' => $email,
                ':vip_options' => json_encode($vipOptions)
            ]);

            $sql = "INSERT INTO reservations (user_id, event_id, ticket_id, quantity, total_price, status, full_name, email, vip_options) 
                    VALUES (:user_id, :event_id, :ticket_id, :quantity, :total_price, :status, :full_name, :email, :vip_options)";
            error_log("Exécution de la requête : " . $sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':event_id' => $eventId,
                ':ticket_id' => $ticketId,
                ':quantity' => $quantity,
                ':total_price' => $totalPrice,
                ':status' => 'reserved',
                ':full_name' => $fullName,
                ':email' => $email,
                ':vip_options' => json_encode($vipOptions)
            ]);
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':event_id' => $eventId,
                ':ticket_id' => $ticketId, // Maintenant c'est un entier !
                ':quantity' => $quantity,
                ':total_price' => $totalPrice,
                ':status' => 'reserved',
                ':full_name' => $fullName,
                ':email' => $email,
                ':vip_options' => json_encode($vipOptions)
            ]);
    
            // Retourner l'ID de la dernière insertion
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log('Erreur lors de l\'ajout de la réservation: ' . $e->getMessage());
            throw new \Exception("Erreur lors de l'ajout de la réservation: " . $e->getMessage());
        }
    }
    

    public function updateReservationStatus($reservationId, $status, $qrCode = null) {
        try {
            $sql = "UPDATE reservations 
                    SET status = :status" . 
                    ($qrCode ? ", qr_code = :qr_code" : "") . 
                    " WHERE id = :id";
            
            $params = [':status' => $status, ':id' => $reservationId];
            if ($qrCode) {
                $params[':qr_code'] = $qrCode;
            }

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la mise à jour de la réservation: " . $e->getMessage());
        }
    }

   
    private function getTicketIdByType($ticketType) {
        $query = "SELECT id FROM tickets WHERE type = :ticketType";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':ticketType', $ticketType);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    
    
}