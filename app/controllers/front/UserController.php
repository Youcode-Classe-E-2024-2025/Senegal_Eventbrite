<?php

namespace Controller_front;

use Core\Controller;

class UserController extends Controller {
    public function index (){
        $this->view("front/userDash");
    }

    public function event(){
        $this->view("front/create_event");
    }

    public function info(){
        $this->view("front/profileInfo");
    }
}