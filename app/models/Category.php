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

    public function deleteCategory($id)
    {
        return $this->delete("categorys" , $id);
    }
}
