<?php

namespace Model;

use Core\Model;

class Promo extends Model {
    public function insertPromo($data){
        return $this->insert("promo", $data);
    }
    
    public function getAllPromosWithEvents() {
        $query = "SELECT p.*, e.title as event_title 
                 FROM promo p 
                 JOIN events e ON p.event_id = e.id 
                 ORDER BY p.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function deletePromo($id) {
        return $this->delete('promo', $id);
    }
}