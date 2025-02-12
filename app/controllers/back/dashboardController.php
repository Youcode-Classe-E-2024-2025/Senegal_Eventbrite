<?php
namespace Controller_back;
use Core\Controller;
use Model\Admin;
use Model\Category;
use Model\Event;

class dashboardController extends Controller{
    public function dashboard() {
    $usermodel = new Admin();
    $categorymodel = new Category();
    $eventsModel = new Event();

    $limit = 3;
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $users = $usermodel->getUsersPaginated($limit, $offset);
    $totalUsers = $usermodel->countUsers();
    $totalPages = ceil($totalUsers / $limit);
    $categorys = $categorymodel->getAllCategory();
    $events = $eventsModel->getAllEvent();
    $totalRevenue = $eventsModel->totalRevenueGlobal();

    $this->view('back/dashboard', [
        'users' => $users,
        'events' => $events,
        'totalRevenue' => $totalRevenue,
        'categorys' => $categorys,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ]);
}
    public function deleteEvent()
    {
        $id = $_POST['id'] ?? null ;

        if (!$id) {
            echo "ID non valide.";
            return;
        }

        $categoryModel= new Event();
        $categoryModel->deleteEvent($id);

        header("Location: /admin");
        exit();
    }


}