<?php
namespace Model;
use Core\Model;
use PDO;

class Event extends Model{

    public function getAll(){
        return $this->fetchAll("events");
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
            WHERE status = 'ACTIVE' 
            AND isActif = TRUE
            ORDER BY date_start ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFilteredEvents($categoryId, $dateFilter, $priceFilter, $searchQuery)
{
    // Join the reservations count like in getActiveEvents
    $query = "SELECT events.*, 
              (SELECT COUNT(*) FROM reservations WHERE event_id = events.id) as participants_count 
              FROM events 
              WHERE 1=1";
    $params = [];

    if ($categoryId) {
        $query .= " AND category = :category";
        $params[':category'] = $categoryId;
    }

    if ($priceFilter === 'free') {
        $query .= " AND price = 0";
    } elseif ($priceFilter === 'paid') {
        $query .= " AND price > 0";
    }

    if ($searchQuery) {
        // Remove PostgreSQL-specific cast if using MySQL
        $query .= " AND (title LIKE :search OR artist_name LIKE :search OR tags LIKE :search)";
        $params[':search'] = "%$searchQuery%";
    }

    $stmt = $this->db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function getSearchSuggestions($query)
    {
        $stmt = $this->db->prepare("
            SELECT title FROM events 
            WHERE title ILIKE :query 
            AND status = 'ACTIVE'
            LIMIT 5
        ");
        $stmt->execute([':query' => "%$query%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}