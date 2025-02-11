<?php
namespace Controller_back;
use Core\Controller;
use Model\Category;
use Model\User as User;

class dashboardController extends Controller{
    public function dashboard(){
        $usermodel = new User();
        $categorymodel = new Category();


        $users = $usermodel->getAllUsers();
        $categorys = $categorymodel->getAllCategory();


        $this->view('back/dashboard', ['users' => $users , 'categorys' => $categorys]);
    }
}