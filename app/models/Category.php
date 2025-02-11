<?php
namespace Model;

use Core\Model;

class Category extends Model {

    public function addCategory($title, $imagePath) {
        return $this->insert("categorys", [
            'title' => $title,
            'image' => $imagePath
        ]);
    }

    public function getAllCategory()
    {
       return $this->fetchAll("categorys");
    }
}
