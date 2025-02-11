<?php

namespace Models;

use PDO;

class Reservations {
    private $db;

    // Constructeur pour initialiser la connexion à la base de données
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Ajouter une nouvelle réservation
    public function addReservation($user_id, $event_id, $ticket_type, $quantity, $total_price, $qr_code, $status) {
        try {
            if (!is_numeric($user_id) || !is_numeric($event_id) || !is_numeric($quantity) || !is_numeric($total_price)) {
                throw new \Exception("Données invalides.");
            }

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

            if (!$stmt->execute()) {
                throw new \Exception("Échec de l'insertion de la réservation.");
            }

            error_log("Réservation ajoutée avec succès.");
            return true;
        } catch (\Exception $e) {
            error_log("Erreur lors de l'ajout de la réservation : " . $e->getMessage());
            return false;
        }
    }

    // Mettre à jour une réservation existante
    public function updateReservation($id, $ticket_type, $quantity, $total_price, $qr_code, $status) {
        try {
            if (!is_numeric($id) || !is_numeric($quantity) || !is_numeric($total_price)) {
                throw new \Exception("Données invalides.");
            }

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

            if (!$stmt->execute()) {
                throw new \Exception("Échec de la mise à jour de la réservation.");
            }

            error_log("Réservation mise à jour avec succès.");
            return true;
        } catch (\Exception $e) {
            error_log("Erreur lors de la mise à jour de la réservation : " . $e->getMessage());
            return false;
        }
    }

    // Supprimer une réservation par son ID
    public function deleteReservation($id) {
        try {
            if (!is_numeric($id)) {
                throw new \Exception("ID invalide.");
            }

            $query = "DELETE FROM reservations WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                error_log("Réservation avec l'ID $id supprimée avec succès.");
                return true;
            } else {
                error_log("Échec de la suppression de la réservation avec l'ID $id.");
                return false;
            }
        } catch (\Exception $e) {
            error_log("Erreur lors de la suppression de la réservation : " . $e->getMessage());
            return false;
        }
    }

    // Récupérer toutes les réservations avec pagination
    public function getAllReservations($limit = 50, $offset = 0) {
        try {
            $query = "SELECT * FROM reservations LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération des réservations : " . $e->getMessage());
            return [];
        }
    }

    // Compter le nombre total de réservations
    public function countReservations() {
        try {
            $query = "SELECT COUNT(*) AS total FROM reservations";
            $stmt = $this->db->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (\Exception $e) {
            error_log("Erreur lors du comptage des réservations : " . $e->getMessage());
            return 0;
        }
    }

    // Récupérer une réservation par son ID
    public function getReservationById($id) {
        try {
            if (!is_numeric($id)) {
                throw new \Exception("ID invalide.");
            }

            $query = "SELECT * FROM reservations WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération de la réservation : " . $e->getMessage());
            return null;
        }
    }

    // Récupérer les réservations d'un utilisateur spécifique
    public function getUserReservations($user_id) {
        try {
            if (!is_numeric($user_id)) {
                throw new \Exception("ID utilisateur invalide.");
            }

            $query = "SELECT * FROM reservations WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération des réservations de l'utilisateur : " . $e->getMessage());
            return [];
        }
    }

    // Récupérer les réservations pour un événement spécifique
    public function getEventReservations($event_id) {
        try {
            if (!is_numeric($event_id)) {
                throw new \Exception("ID événement invalide.");
            }

            $query = "SELECT * FROM reservations WHERE event_id = :event_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':event_id', $event_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération des réservations pour l'événement : " . $e->getMessage());
            return [];
        }
    }

    // Filtrer les réservations selon des critères
    public function filterReservations($filters = []) {
        try {
            $query = "SELECT * FROM reservations WHERE 1=1";

            if (isset($filters['user_id'])) {
                $query .= " AND user_id = :user_id";
            }
            if (isset($filters['event_id'])) {
                $query .= " AND event_id = :event_id";
            }
            if (isset($filters['status'])) {
                $query .= " AND status = :status";
            }

            $stmt = $this->db->prepare($query);

            if (isset($filters['user_id'])) {
                $stmt->bindParam(':user_id', $filters['user_id']);
            }
            if (isset($filters['event_id'])) {
                $stmt->bindParam(':event_id', $filters['event_id']);
            }
            if (isset($filters['status'])) {
                $stmt->bindParam(':status', $filters['status']);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erreur lors du filtrage des réservations : " . $e->getMessage());
            return [];
        }
    }

    // Ajouter une réservation avec transaction
    public function addReservationWithTransaction($user_id, $event_id, $ticket_type, $quantity, $total_price, $qr_code, $status) {
        try {
            $this->db->beginTransaction();

            // Ajoute la réservation
            $this->addReservation($user_id, $event_id, $ticket_type, $quantity, $total_price, $qr_code, $status);

            // Hypothétique méthode pour mettre à jour le stock des billets
            $this->updateTicketStock($event_id, $ticket_type, -$quantity);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors de l'ajout de la réservation avec transaction : " . $e->getMessage());
            return false;
        }
    }

    // Méthode hypothétique pour mettre à jour le stock des billets
    private function updateTicketStock($event_id, $ticket_type, $quantity) {
        try {
            $query = "UPDATE tickets SET stock = stock + :quantity WHERE event_id = :event_id AND type = :ticket_type";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':event_id', $event_id);
            $stmt->bindParam(':ticket_type', $ticket_type);

            if (!$stmt->execute()) {
                throw new \Exception("Échec de la mise à jour du stock des billets.");
            }
        } catch (\Exception $e) {
            error_log("Erreur lors de la mise à jour du stock des billets : " . $e->getMessage());
            throw $e;
        }
    }
}