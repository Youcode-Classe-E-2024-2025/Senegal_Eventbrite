<?php
namespace Controller_back;
use Core\Controller;
use Model\Admin;
use Model\Category;

class dashboardController extends Controller{
    public function dashboard() {
    $usermodel = new Admin();
    $categorymodel = new Category();

    // Nombre d'utilisateurs par page
    $limit = 3;
    
    // Page actuelle (si non définie, par défaut 1)
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    // Calcul de l'offset pour la requête SQL
    $offset = ($page - 1) * $limit;

    // Récupérer les utilisateurs avec pagination
    $users = $usermodel->getUsersPaginated($limit, $offset);

    // Récupérer le nombre total d'utilisateurs
    $totalUsers = $usermodel->countUsers();

    // Calcul du nombre total de pages
    $totalPages = ceil($totalUsers / $limit);

    // Récupérer les catégories (inchangé)
    $categorys = $categorymodel->getAllCategory();

    // Passer les données à la vue
    $this->view('back/dashboard', [
        'users' => $users,
        'categorys' => $categorys,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ]);
}

}