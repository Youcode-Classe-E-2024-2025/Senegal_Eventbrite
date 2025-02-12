<?php
namespace Model;
use Core\Model;

class Event extends Model{

    public function getAllEvent($userId)
{
    $query = "SELECT * FROM events WHERE organizer_id = ?";
    $stmt = $this->db->prepare($query);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function getEventById($id)
{
    $query = "SELECT * FROM events WHERE id = ?";
    $stmt = $this->db->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

    public function deleteEvent($id)
    {
        return $this->delete("events" , $id);
    }
}