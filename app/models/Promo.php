<?php

namespace Model;

use Core\Model;
use PDO;

class Promo extends Model {
    public function insertPromo($data){
        return $this->insert("promo", $data);
    }
    
    public function getAllPromosWithEvents($userId)
    {
        $query = "SELECT p.*, e.title as event_title 
                FROM promo p 
                JOIN events e ON p.event_id = e.id 
                WHERE e.organizer_id = ?
                ORDER BY p.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deletePromo($id) {
        return $this->delete('promo', $id);
    }

    public function getPromoByCode($code) {
        $stmt = $this->db->prepare("SELECT * FROM promo WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function decrementUsage($id) {
        $stmt = $this->db->prepare("UPDATE promo SET usage_limit = usage_limit - 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}