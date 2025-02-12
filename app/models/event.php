<?php
namespace Model;
use Core\Model;

class Event extends Model{

    public function getAllEvent()
    {
        return $this->fetchAll("events");
    }

    public function deleteEvent($id)
    {
        return $this->delete("events" , $id);
    }
}