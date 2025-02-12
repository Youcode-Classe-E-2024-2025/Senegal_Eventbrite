<?php

namespace Controller_front;

use Core\Controller;
use Model\Event;
use Model\Promo;
use Model\User;

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

    public function info(){
        $this->view("front/profileInfo", ['user' => $_SESSION['user']]);
    }

    public function updateProfileImage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Ensure user is logged in
                if (!isset($_SESSION['user']['id'])) {
                    throw new \Exception("User must be logged in.");
                }
                $userId = $_SESSION['user']['id'];
    
                // Verify file upload exists and has no errors
                // if (!isset($_FILES['profile_image']) {
                //     throw new \Exception("No file uploaded.");
                // }
    
                $file = $_FILES['profile_image'];
                
                // Validate upload error code
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new \Exception("File upload error: " . $file['error']);
                }
    
                // Validate file is an actual image
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);
                if (!str_starts_with($mime, 'image/')) {
                    throw new \Exception("Invalid file type. Only images are allowed.");
                }
    
                // Configure paths
                $publicRelativePath = '/assets/uploads/userAvatar/';
                $serverAbsolutePath = $_SERVER['DOCUMENT_ROOT'] . $publicRelativePath;
    
                // Create directory if needed
                if (!is_dir($serverAbsolutePath)) {
                    mkdir($serverAbsolutePath, 0777, true);
                }
    
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = uniqid('avatar_', true) . '.' . $extension;
                $fullServerPath = $serverAbsolutePath . $fileName;
    
                // Move uploaded file
                if (!move_uploaded_file($file['tmp_name'], $fullServerPath)) {
                    throw new \Exception("Failed to save uploaded file.");
                }
    
                // Update database with web-accessible path
                $userModel = new User();
                $webAccessiblePath = $publicRelativePath . $fileName;
                
                if (!$userModel->update(['avatar_url' => $webAccessiblePath], $userId)) {
                    // If update failed, clean up the uploaded file
                    unlink($fullServerPath);
                    throw new \Exception("Failed to update database record.");
                }
    
                // Update session and respond
                $_SESSION['user']['avatar_url'] = $webAccessiblePath;
                $_SESSION['success'] = "Profile image updated successfully!";
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            
            // Redirect back to profile page
            header("Location: /profileInfo");
            exit();
        }
    }

    /**
     * Update the user's password.
     */
    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Ensure the user is logged in.
                if (!isset($_SESSION['user']['id'])) {
                    throw new \Exception("User must be logged in.");
                }
                $userId = $_SESSION['user']['id'];

                // Retrieve POST data.
                $currentPassword = $_POST['current_password'];
                $newPassword     = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];

                // Validate that the new password and confirmation match.
                if ($newPassword !== $confirmPassword) {
                    throw new \Exception("New password and confirmation do not match.");
                }

                // Retrieve the current user record.
                $userModel = new User();
                $user = $userModel->find($userId); // Assumes a find($id) method exists.
                if (!$user) {
                    throw new \Exception("User not found.");
                }

                // Verify that the provided current password matches the stored hash.
                if (!password_verify($currentPassword, $user['password'])) {
                    throw new \Exception("Current password is incorrect.");
                }

                // Hash the new password.
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                // Update the password in the database.
                $updateData = ['password' => $hashedPassword];
                $userModel->update( $updateData, $userId);

                $_SESSION['success'] = "Password updated successfully!";
                header("Location: /profileInfo");
                exit();
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: /profileInfo");
                exit();
            }
        }
    }
}