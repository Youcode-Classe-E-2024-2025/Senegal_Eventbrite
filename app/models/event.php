<?php
namespace Model;
use Core\Model;
use PDO;

class Event extends Model{

    public function getAll(){
        $stmt = $this->db->prepare("
            SELECT events.*, users.name AS organizer_name
            FROM events
            JOIN users ON events.organizer_id = users.id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCategoryTitleById($id) {
        $stmt = $this->db->prepare("SELECT title FROM categorys WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetchColumn(); // Returns the title if found or false
    }
    
    public function getAllEvent($userId)
    {
        $query = "SELECT * FROM events WHERE organizer_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventById($id)
    {
        $query = "SELECT * FROM events WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteEvent($id)
    {
        return $this->delete("events" , $id);
    }

    public function totalRevenueGlobal() {
        $stmt = $this->db->prepare("SELECT SUM(r.quantity * e.price) AS total_revenue_global
                                    FROM reservations r
                                    JOIN events e ON r.event_id = e.id
                                    WHERE r.status = 'reserved'
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue_global'] ?? 0;
    }

    public function getSalesByUser($userId) {
        $query = "SELECT e.title, e.capacity, es.tickets_sold, es.revenue, e.status 
                  FROM events e 
                  JOIN event_statistics es ON e.id = es.event_id 
                  WHERE e.organizer_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveEvents()
    {
        $stmt = $this->db->prepare("SELECT *, 
                   (SELECT COUNT(*) FROM reservations 
                    WHERE event_id = events.id) as participants_count
            FROM events 
            WHERE isActif = TRUE
            ORDER BY date_start ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

// In Event model
public function getFilteredEvents($searchTerm = null, $categoryId = null, $priceFilter = null) {
    $baseQuery = "SELECT * FROM events WHERE isActif = TRUE AND status = 'ACTIVE'";
    $conditions = [];
    $params = [];

    // Search filter
    if (!empty($searchTerm)) {
        $conditions[] = "(title ILIKE :searchTerm OR artist_name ILIKE :searchTerm OR EXISTS (SELECT 1 FROM unnest(tags) tag WHERE tag ILIKE :searchTerm))";
        $params['searchTerm'] = "%$searchTerm%";
    }

    // Category filter - convert category ID to category title
    if (!empty($categoryId)) {
        $categoryTitle = $this->getCategoryTitleById($categoryId);
        if ($categoryTitle) {
            $conditions[] = "category = :categoryTitle";
            $params['categoryTitle'] = $categoryTitle;
        }
    }

    // Price filter
    if ($priceFilter === 'free') {
        $conditions[] = "price = 0";
    } elseif ($priceFilter === 'paid') {
        $conditions[] = "price > 0";
    }

    // Combine conditions
    if (!empty($conditions)) {
        $baseQuery .= " AND " . implode(" AND ", $conditions);
    }

    $stmt = $this->db->prepare($baseQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}