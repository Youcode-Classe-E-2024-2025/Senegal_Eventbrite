<?php

namespace Model;

use Core\Database;

class User
{
    public $username;
    public $email;
    public $password;

    public function save()
    {
        $db = Database::getInstance()->getConnection();

        $query = 'SELECT * FROM users WHERE email = :email LIMIT 1';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return false;
        }

        $query = 'INSERT INTO users (role, email, password, name, avatar_url) 
                  VALUES (:role, :email, :password, :name, :avatar_url)';

        $stmt = $db->prepare($query);

        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':avatar_url', $avatar_url);

        $role = 'user';
        $name = $this->username;
        $avatar_url = '';

        $stmt->execute();
    }

    public static function findByEmail($email) {
        $db = Database::getInstance()->getConnection();
        $query = 'SELECT * FROM users WHERE email = :email LIMIT 1';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetchObject(self::class);
        return $result;
    }

}