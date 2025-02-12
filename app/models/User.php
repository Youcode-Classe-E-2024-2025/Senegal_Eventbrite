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

        $role = 'user';
        $name = $this->username;
        $avatar_url = 'assets/uploads/userAvatar/5856.jpg';

        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':avatar_url', $avatar_url);


        $stmt->execute();
    }

    public static function findByEmail($email)
    {
        $db = Database::getInstance()->getConnection();
        $query = 'SELECT * FROM users WHERE email = :email LIMIT 1';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetchObject(self::class);
        return $result;
    }

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createUser(array $data)
    {
        $stmt = $this->db->prepare("INSERT INTO users (email, name, avatar_url, role, password) VALUES (:email, :name, :avatar_url, :role, :password)");
        $stmt->execute([
            ':email' => $data['email'],
            ':name' => $data['name'],
            ':avatar_url' => $data['avatar_url'],
            ':role' => $data['role'],
            ':password' => $data['password'],
        ]);
        return $this->db->lastInsertId();
    }

}
