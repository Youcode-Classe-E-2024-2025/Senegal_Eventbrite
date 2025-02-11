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

            // Vérification du format de l'image
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileInfo = pathinfo($_FILES['image']['name']);
            $extension = strtolower($fileInfo['extension']);

            if (!in_array($extension, $allowedExtensions)) {
                echo "Format d'image non valide. Seuls JPG, PNG, GIF sont autorisés.";
                return;
            }

            // Générer un nom de fichier unique
            $imagePath = $imageDir . uniqid() . '.' . $extension;

            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                echo "L'image est trop grande (max 2MB).";
                return;
            }

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
                // Insérer la catégorie dans la base de données
                $categoryModel = new Category();
                $categoryModel->addCategory($title, $imagePath);

                // Redirection après succès
                header("Location: /admin");
                exit();
            } else {
                echo "Erreur lors du téléchargement de l'image.";
            }
        }

        // Afficher le formulaire
        $this->view('back/dashboard');
    }
}
