<?php

namespace Controller_front;

use Core\Controller;
use Model\Category;
use Model\Event;
use Model\Promo;

class UserController extends Controller {
    public function index() {
        $eventModel = new Event();
        $promoModel = new Promo();

        $userId = $_SESSION['user']["id"] ?? null;
        
        $events = $eventModel->getAllEvent($userId);
        $promos = $promoModel->getAllPromosWithEvents($userId);
        
        $this->view("front/userDash", [
            'events' => $events,
            'promos' => $promos
        ]);
    }

    public function event(){
        $categoryModel = new Category();
        $categories = $categoryModel->getAllCategory();
        $this->view("front/create_event", [
            'categories' => $categories
        ]);
    }

    public function info(){
        $this->view("front/profileInfo");
    }
}