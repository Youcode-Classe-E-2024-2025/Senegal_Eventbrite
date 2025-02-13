<?php

namespace Core;

use PDO;
use Exception;

class Payments {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Crée un nouvel enregistrement de paiement
     */
    public function createPaymentRecord($data) {
        $query = "INSERT INTO payments (
            reservation_id, 
            stripe_session_id, 
            amount, 
            status, 
            metadata
        ) VALUES (
            :reservation_id, 
            :stripe_session_id, 
            :amount, 
            :status, 
            :metadata
        ) RETURNING id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':reservation_id' => $data['reservation_id'],
                ':stripe_session_id' => $data['stripe_session_id'],
                ':amount' => $data['amount'],
                ':status' => $data['status'],
                ':metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null
            ]);

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Erreur lors de la création du paiement: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Met à jour le statut d'un paiement avec des métadonnées optionnelles
     */
    public function updatePaymentStatus($id, $status, $metadata = null) {
        $query = "UPDATE payments 
                 SET status = :status, 
                     metadata = CASE 
                         WHEN :metadata::jsonb IS NOT NULL 
                         THEN metadata || :metadata::jsonb 
                         ELSE metadata 
                     END,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id' => $id,
                ':status' => $status,
                ':metadata' => $metadata
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour du statut de paiement: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupère un paiement par son ID
     */
    public function getPaymentById($id) {
        $query = "SELECT p.*, r.full_name, r.email, r.ticket_type, r.quantity 
                 FROM payments p
                 JOIN reservations r ON p.reservation_id = r.id
                 WHERE p.id = :id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération du paiement: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupère un paiement par son ID de session Stripe
     */
    public function getPaymentByStripeSessionId($sessionId) {
        $query = "SELECT p.*, r.full_name, r.email, r.ticket_type, r.quantity 
                 FROM payments p
                 JOIN reservations r ON p.reservation_id = r.id
                 WHERE p.stripe_session_id = :session_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':session_id' => $sessionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération du paiement par session ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupère tous les paiements pour une réservation
     */
    public function getPaymentsByReservationId($reservationId) {
        $query = "SELECT * FROM payments WHERE reservation_id = :reservation_id ORDER BY created_at DESC";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':reservation_id' => $reservationId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des paiements de la réservation: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vérifie si un paiement existe pour une session Stripe
     */
    public function checkStripeSessionExists($sessionId) {
        $query = "SELECT COUNT(*) FROM payments WHERE stripe_session_id = :session_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':session_id' => $sessionId]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Erreur lors de la vérification de la session Stripe: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Marque un paiement comme remboursé
     */
    public function markPaymentAsRefunded($paymentId, $refundData) {
        try {
            $this->db->beginTransaction();

            // Mettre à jour le statut du paiement
            $this->updatePaymentStatus($paymentId, 'refunded', json_encode([
                'refund_id' => $refundData['id'],
                'refund_amount' => $refundData['amount'],
                'refund_date' => date('Y-m-d H:i:s'),
                'refund_reason' => $refundData['reason'] ?? null
            ]));

            // Mettre à jour le statut de la réservation associée
            $query = "UPDATE reservations 
                     SET status = 'cancelled', 
                         updated_at = CURRENT_TIMESTAMP 
                     WHERE id = (
                         SELECT reservation_id 
                         FROM payments 
                         WHERE id = :payment_id
                     )";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':payment_id' => $paymentId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors du marquage du remboursement: " . $e->getMessage());
            throw $e;
        }
    }
}