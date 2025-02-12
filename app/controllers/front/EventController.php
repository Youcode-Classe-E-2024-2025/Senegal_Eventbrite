<?php

namespace Controller_front;

use Core\Controller;
use Model\Category;

class EventController extends Controller{

    public function store() {
        if ($_SERVER["REQUEST_METHOD"] === "POST"){
            
        }
    }

    public function event(){
        $categoryModel = new Category();
        $categories = $categoryModel->getAllCategory();
        $this->view("front/create_event", [
            'categories' => $categories
        ]);
    }
}