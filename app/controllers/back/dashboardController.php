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
        $totaleuser = $usermodel->getAllUsers();
        $totalUsers = $usermodel->countUsers();
        $totalPages = ceil($totalUsers / $limit);
        $categorys = $categorymodel->getAllCategory();
        $events = $eventsModel->getAll();
        $totalRevenue = $eventsModel->totalRevenueGlobal();
    
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            // Respond with only the updated table and pagination
            echo $this->view('back/dashboard', [
                'users' => $users,
                'totalPages' => $totalPages,
                'currentPage' => $page
            ]);
        } else {
            // Standard response for normal page load
            $this->view('back/dashboard', [
                'users' => $users,
                'events' => $events,
                'totalRevenue' => $totalRevenue,
                'categorys' => $categorys,
                'totalPages' => $totalPages,
                "totaluser" => $totaleuser,
                'currentPage' => $page
            ]);
        }
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

    public function updateStatus()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['id'], $data['is_active'])) {
            $userId = intval($data['id']);
            $isActive = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            $usermodel = new Admin();
            $result = $usermodel->updateUserStatus($userId, $isActive);

            if ($result) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Aucune modification effectuée."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Données invalides."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    }
}



}