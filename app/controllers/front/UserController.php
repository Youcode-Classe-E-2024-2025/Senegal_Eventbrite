<?php

namespace Controller_front;

use Core\Controller;
use Exception;
use Model\Event;
use Model\Promo;
use Model\User;
use PHPMailer\PHPMailer\PHPMailer;

class UserController extends Controller
{
    public function index()
    {
        $eventModel = new Event();
        $promoModel = new Promo();

        $userId = $_SESSION['user']["id"] ?? null;

        $events = $eventModel->getAllEvent($userId);
        $promos = $promoModel->getAllPromosWithEvents($userId);
        $sales = $eventModel->getSalesByUser($userId);

        $this->view("front/userDash", [
            'events' => $events,
            'promos' => $promos,
            'sales' => $sales,
        ]);
    }

    public function info()
    {
        $this->view("front/profileInfo", ['user' => $_SESSION['user']]);
    }

    public function updateProfileImage()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Ensure user is logged in
                if (!isset($_SESSION['user']['id'])) {
                    throw new Exception("User must be logged in.");
                }
                $userId = $_SESSION['user']['id'];

                // Verify file upload exists and has no errors
                // if (!isset($_FILES['profile_image']) {
                //     throw new Exception("No file uploaded.");
                // }

                $file = $_FILES['profile_image'];

                // Validate upload error code
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("File upload error: " . $file['error']);
                }

                // Validate file is an actual image
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);
                if (!str_starts_with($mime, 'image/')) {
                    throw new Exception("Invalid file type. Only images are allowed.");
                }

                // Configure paths
                $publicRelativePath = '/public/assets/uploads/userAvatar/';
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
                    throw new Exception("Failed to save uploaded file.");
                }

                // Update database with web-accessible path
                $userModel = new User();
                $webAccessiblePath = $publicRelativePath . $fileName;

                if (!$userModel->update(['avatar_url' => $webAccessiblePath], $userId)) {
                    // If update failed, clean up the uploaded file
                    unlink($fullServerPath);
                    throw new Exception("Failed to update database record.");
                }

                // Update session and respond
                $_SESSION['user']['avatar_url'] = $webAccessiblePath;
                $_SESSION['success'] = "Profile image updated successfully!";

            } catch (Exception $e) {
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
    public function updatePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Ensure the user is logged in.
                if (!isset($_SESSION['user']['id']) || !isset($_SESSION['user']['email']) || !isset($_SESSION['user']['name'])) {
                    throw new Exception("User must be logged in.");
                }

                $userId = $_SESSION['user']['id'];
                $userEmail = $_SESSION['user']['email'];
                $userName = $_SESSION['user']['name'];

                // Retrieve POST data.
                $currentPassword = $_POST['current_password'];
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];

                // Validate that the new password and confirmation match.
                if ($newPassword !== $confirmPassword) {
                    throw new Exception("New password and confirmation do not match.");
                }

                // Retrieve the current user record.
                $userModel = new User();
                $user = $userModel->find($userId); // Assumes a find($id) method exists.
                if (!$user) {
                    throw new Exception("User not found.");
                }

                // Verify that the provided current password matches the stored hash.
                if (!password_verify($currentPassword, $user['password'])) {
                    throw new Exception("Current password is incorrect.");
                }

                // Hash the new password.
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                // Update the password in the database.
                $updateData = ['password' => $hashedPassword];
                $userModel->update($updateData, $userId);

                // Send email notification
                $this->sendPasswordUpdateEmail($userEmail, $userName);

                $_SESSION['success'] = "Password updated successfully!";
                header("Location: /profileInfo");
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: /profileInfo");
                exit();
            }
        }
    }

    private function sendPasswordUpdateEmail($email, $name)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_Host'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_Username'];
            $mail->Password = $_ENV['SMTP_Password'];
            $mail->SMTPSecure = $_ENV['SMTP_Secure'];
            $mail->Port = $_ENV['SMTP_Port'];

            $mail->setFrom($_ENV['SMTP_Username'], 'ZHOO Security');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Has Been Changed';

            $mail->Body = '
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    color: #e0e0e0;
                    background-color: #121212;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    padding: 20px;
                    border: 1px solid #333;
                    border-radius: 5px;
                    width: 90%;
                    max-width: 600px;
                    margin: auto;
                    background-color: #1e1e1e;
                }
                .header {
                    background-color: #0e1111;
                    color: #ffffff;
                    padding: 14px;
                    text-align: center;
                    font-size: 20px;
                    font-weight: bold;
                    border-radius: 5px 5px 0 0;
                }
                .content {
                    padding: 20px;
                    font-size: 16px;
                    line-height: 1.6;
                    color: #d4d4d4;
                }
                .footer {
                    margin-top: 20px;
                    font-size: 14px;
                    color: #888888;
                    text-align: center;
                    border-top: 1px solid #333;
                    padding-top: 10px;
                }
                a {
                    background-color: yellow;
                    padding: 10px;
                    border-radius: 10px;
                    color: black;
                    text-decoration: none;
                }
                p {
                    color: #888888;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://lh3.googleusercontent.com/pw/AP1GczMv2wyQdFNW7zVUC0W4vUVSOTl7yWWPf718uoa3c3Wlw2gCwANtImpdZcKPYmOoZ2ruqMcr4FHtFKm6sUoEC_9oBMG7wqOPDjH6arfHLkHJQd7EP-QA2BB9JL7KKRBl1r1_Lv7PMdxhJVN2svmC41ob=w500-h210-s-no-gm?authuser=0" width="150" alt="ZHOO Logo">
                </div>
                <div class="content">
                    <p>Dear <strong>' . htmlspecialchars($name) . '</strong>,</p>
                    <p>Your password has been successfully updated. If you did not make this change, please reset your password immediately.</p>
                    <p><strong>Time of Change:</strong> ' . date("Y-m-d H:i:s") . '</p>
                    <p>For security reasons, we recommend using a strong and unique password.</p>
                    <a href="http://localhost/resetPassword">Reset Password</a>
                </div>
                <div class="footer">
                    &copy; ' . date("Y") . ' ZHOO Security. All rights reserved.
                </div>
            </div>
        </body>
        </html>';

            $mail->send();
        } catch (Exception $e) {
            error_log("Password update email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

}