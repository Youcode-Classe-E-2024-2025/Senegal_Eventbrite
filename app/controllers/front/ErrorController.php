<?php

namespace Controller_front;

use Core\Controller;

class ErrorController extends Controller {
    public function index(){
        $this->view("/error/403");
    }
}