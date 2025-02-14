<?php

namespace Model;

use Core\Model;

class EventStatistics extends Model{
    public function incrementTicketsSold($eventId, $quantity) {
        $stmt = $this->db->prepare("UPDATE event_statistics SET tickets_sold = tickets_sold + ? WHERE event_id = ?");
        $stmt->execute([$quantity, $eventId]);
    }
    
    public function incrementRevenue($eventId, $amount) {
        $stmt = $this->db->prepare("UPDATE event_statistics SET revenue = revenue + ? WHERE event_id = ?");
        $stmt->execute([$amount, $eventId]);
    }
}