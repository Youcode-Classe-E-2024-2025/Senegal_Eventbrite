<?php 

namespace Model;

use Core\Model;

class User extends Model {


    public function getRoles($id){
        
    }

    public function getAllUsers()
    {
       return $this->fetchAll("users");
    }
}