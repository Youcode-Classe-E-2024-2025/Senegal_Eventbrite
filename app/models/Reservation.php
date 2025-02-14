<?php

namespace Model;

use Core\Model;
use PDO;

class Reservation extends Model {
    protected $table = 'reservations';

    public function getAllReservations() {
        return $this->fetchAll($this->table);
    }

    public function createReservation($data) {
        return $this->insert($this->table, $data, true);
    }

    public function updateReservation($id, $data) {
        return $this->update($this->table, $data, $id);
    }

    public function deleteReservation($id) {
        return $this->delete($this->table, $id);
    }

    public function getReservationsByUserId($userId) {
        $stmt = $this->db->prepare("SELECT r.*, e.title, e.artist_name AS artist, e.date_start AS date, e.price
                                    FROM {$this->table} r
                                    JOIN events e ON r.event_id = e.id
                                    WHERE r.user_id = :user_id AND r.status != 'canceled'
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function cancelReservation($id) {
        return $this->update($this->table, ['status' => 'canceled'], $id);
    }

    public function getQrCodeByReservationId($id) {
        $stmt = $this->db->prepare("SELECT qr_code FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['qr_code'] ?? null;
    }

    public function getReservationsCountForEvent($eventId) {
        $stmt = $this->db->prepare("SELECT SUM(quantity) AS total FROM reservations WHERE event_id = ? AND status != 'canceled'");
        $stmt->execute([$eventId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    public function getUserReservationForEvent($userId, $eventId) {
        $stmt = $this->db->prepare("SELECT * FROM reservations WHERE user_id = ? AND event_id = ? AND status != 'canceled'");
        $stmt->execute([$userId, $eventId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}