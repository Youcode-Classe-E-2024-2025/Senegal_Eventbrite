<?php 

namespace Model;

use Core\Database;
use Core\Model;
use PDO;

class Admin extends Model {

    public function getRoles($id){
        
    }

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


}
    
