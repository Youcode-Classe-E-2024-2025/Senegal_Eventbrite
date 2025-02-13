<?php 

namespace Model;

use Core\Database;
use Core\Model;
use PDO;

class Admin extends Model {

    public function getAllUsers()
    {
       return $this->fetchAll("users");
    }

    public function getUsersPaginated($limit, $offset)
    {
        return $this->getPaginated("users" , $limit , $offset);
    }

    public function countUsers(){
        return $this->count("users");
    }

    public function updateUserStatus($userId, $isActive)
{
    $stmt = $this->db->prepare("UPDATE users SET is_active = :is_active WHERE id = :id");
    return $stmt->execute(['is_active' => $isActive, 'id' => $userId]);
}



}
    
