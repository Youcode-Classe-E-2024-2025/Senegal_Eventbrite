<?php
namespace Model;
use Core\Model;
use PDO;

class Event extends Model{

    public function getAllEvent()
    {
        return $this->fetchAll("events");
    }

    public function deleteEvent($id)
    {
        return $this->delete("events" , $id);
    }

    public function totalRevenueGlobal() {
        $stmt = $this->db->prepare("
            SELECT SUM(r.quantity * e.price) AS total_revenue_global
            FROM reservations r
            JOIN events e ON r.event_id = e.id
            WHERE r.status = 'reserved'
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue_global'] ?? 0;
    }
    
}