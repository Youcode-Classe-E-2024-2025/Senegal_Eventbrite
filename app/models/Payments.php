<?php

namespace Models;

use PDO;

class Payments {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addPayment($reservation_id, $payment_method, $transaction_id, $amount, $status) {
        $query = "INSERT INTO payments (reservation_id, payment_method, transaction_id, amount, status)
                  VALUES (:reservation_id, :payment_method, :transaction_id, :amount, :status)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':reservation_id', $reservation_id);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':transaction_id', $transaction_id);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function updatePaymentStatus($id, $status) {
        $query = "UPDATE payments SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function getPaymentById($id) {
        $query = "SELECT * FROM payments WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}