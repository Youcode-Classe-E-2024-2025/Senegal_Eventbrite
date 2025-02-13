<?php
namespace Controller_back;

use Core\Controller;
use Model\Category;

class CategoryController extends Controller {

    public function createCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title']);

            if (empty($title)) {
                echo "Le titre est obligatoire.";
                return;
            }

            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                echo "L'image est obligatoire.";
                return;
            }

            $imageDir = "assets/uploads/userEvents/";
            if (!is_dir($imageDir)) {
                mkdir($imageDir, 0777, true);
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileInfo = pathinfo($_FILES['image']['name']);
            $extension = strtolower($fileInfo['extension']);

            if (!in_array($extension, $allowedExtensions)) {
                echo "Format d'image non valide. Seuls JPG, PNG, GIF sont autorisés.";
                return;
            }

            $imagePath = $imageDir . uniqid() . '.' . $extension;

            if ($_FILES['image']['size'] > 10 * 1024 * 1024) {
                echo "L'image est trop grande (max 10MB).";
                return;
            }

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
                $categoryModel = new Category();
                $categoryModel->addCategory($title, $imagePath);

                header("Location: /admin");
                exit();
            } else {
                echo "Erreur lors du téléchargement de l'image.";
            }
        }
        $this->view('back/dashboard');
    } 

    public function deleteCategory(){
        $id = $_POST['id'] ?? null ;

        if (!$id) {
            echo "ID non valide.";
            return;
        }

        $categoryModel= new Category();
        $categoryModel->deleteCategory($id);

        header("Location: /admin");
        exit();
    }
}
