<?php

namespace Controller_front;

use Core\Controller;
use Core\View;
use Core\Auth;
use Core\Validator;
use Model\User;
use Core\Session;
use Exception;
use Google_Client;
use Google_Service_Oauth2;
use PHPMailer\PHPMailer\PHPMailer;
use Detection\MobileDetect;

class AuthController extends Controller
{
    public function signup()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $validator = new Validator();

            $validator->validateRequired('username', $data['username']);
            $validator->validateLength('username', $data['username'], 3, 20);
            $validator->validateRequired('email', $data['email']);
            $validator->validateEmail('email', $data['email']);
            $validator->validateRequired('password', $data['password']);
            $validator->validateLength('password', $data['password'], 6, 100);
            $validator->validateRequired('password_confirmation', $data['password_confirmation']);

            if ($validator->hasErrors()) {
                return $this->view('front/Signup', [
                    'errors' => $validator->getErrors(),
                    'data' => $_POST
                ]);
            }

            $user = new User();
            $user->username = $data['username'];
            $user->email = $data['email'];
            $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
            $user->save();

            Auth::login($user);
            return $this->redirect('/');
        }

        return $this->view('front/Signup');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $validator = new Validator();

            $validator->validateRequired('email', $data['email']);
            $validator->validateEmail('email', $data['email']);
            $validator->validateRequired('password', $data['password']);

            if ($validator->hasErrors()) {
                return $this->view('front/Login', [
                    'errors' => $validator->getErrors(),
                    'data' => $_POST
                ]);
            }

            $user = User::findByEmail($data['email']);

            if (!$user || !password_verify($data['password'], $user->password)) {
                return $this->view('front/Login', [
                    'errors' => [
                        'email' => 'Email or password is incorrect.'
                    ],
                    'data' => $_POST
                ]);
            }

            Auth::login($user);
            return $this->redirect('/');
        }

        return $this->view('front/Login');
    }


    public function signupPost()
    {
        $data = $_POST;

        $validator = new Validator();

        $validator->validateRequired('username', $data['username']);
        $validator->validateLength('username', $data['username'], 3, 20);
        $validator->validateRequired('email', $data['email']);
        $validator->validateEmail('email', $data['email']);
        $validator->validateRequired('password', $data['password']);
        $validator->validateLength('password', $data['password'], 6, 100);

        if ($validator->hasErrors()) {
            return $this->view('front/Signup', [
                'errors' => $validator->getErrors(),
                'data' => $data
            ]);
        }

        $user = new User();
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password = password_hash($data['password'], PASSWORD_BCRYPT);

        try {
            $user->save();
        } catch (Exception $e) {
            return $this->view('front/Signup', [
                'errors' => ['email' => $e->getMessage()],
                'data' => $data
            ]);
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_Host'];
            $mail->SMTPAuth = $_ENV['SMTP_Auth'];
            $mail->Username = $_ENV['SMTP_Username'];
            $mail->Password = $_ENV['SMTP_Password'];
            $mail->SMTPSecure = $_ENV['SMTP_Secure'];
            $mail->Port = $_ENV['SMTP_Port'];

            $mail->setFrom($_ENV['SMTP_Username'], 'ZHOO');
            $mail->addAddress($user->email);
            $mail->isHTML(true);
            $mail->Subject = 'Thank You for Creating an Account!';
            $mail->Body = '
    <html>
    <head>
        <style>
            /* General body styling */
            body {
                font-family: Arial, sans-serif;
                color: #e0e0e0; /* Light text color for dark mode */
                background-color: #121212; /* Dark background */
                margin: 0;
                padding: 20px;
            }

            /* Container styling */
            .container {
                padding: 20px;
                border: 1px solid #333; /* Dark border */
                border-radius: 5px;
                width: 90%;
                max-width: 600px;
                margin: auto;
                background-color: #1e1e1e; /* Slightly lighter background for container */
            }

            /* Header styling */
            .header {
                background-color: #0e1111; /* Darker blue for header */
                color: #ffffff; /* White text */
                padding: 14px;
                text-align: center;
                font-size: 20px;
                font-weight: bold;
                border-radius: 5px 5px 0 0; /* Rounded top corners */
            }

            /* Content styling */
            .content {
                padding: 20px;
                font-size: 16px;
                line-height: 1.6; /* Improved readability */
                color: #d4d4d4; /* Slightly lighter text for content */
            }

            /* Footer styling */
            .footer {
                margin-top: 20px;
                font-size: 14px;
                color: #888888; /* Grayish text for footer */
                text-align: center;
                border-top: 1px solid #333; /* Separator line */
                padding-top: 10px;
            }
            
            a{
              background-color: yellow;
              padding: 10px;
              -webkit-border-radius: 10px;
              color: black;
            }

            p{
              color: #888888;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="header">
                    <img src="https://lh3.googleusercontent.com/pw/AP1GczMv2wyQdFNW7zVUC0W4vUVSOTl7yWWPf718uoa3c3Wlw2gCwANtImpdZcKPYmOoZ2ruqMcr4FHtFKm6sUoEC_9oBMG7wqOPDjH6arfHLkHJQd7EP-QA2BB9JL7KKRBl1r1_Lv7PMdxhJVN2svmC41ob=w500-h210-s-no-gm?authuser=0" width="150" alt="ZHOO Logo">
                </div>
                <p>Dear <strong>' . htmlspecialchars($user->username) . '</strong>,</p>
                <p>Thank you for creating an account with us. We are excited to have you on board!</p>
                <p>Enjoy our services and feel free to reach out if you need any assistance.</p>
                <a href="http://localhost">Click here to access</a>
                <p>Best regards,<br><strong>The ZHOO Team</strong></p>
                <div class="footer">
                    &copy; ' . date("Y") . ' ZHOO. All rights reserved.
                </div>
            </div>
        </div>
    </body>
</html>';

            $mail->send();
        } catch (Exception $e) {
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

        Session::start();
        Session::set('user', [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url
        ]);

        return $this->redirect('/');
    }

    public function loginPost()
    {
        $data = $_POST;

        if (empty($data['email']) || empty($data['password'])) {
            return $this->view('front/Login', [
                'error' => 'Email or password cannot be empty.'
            ]);
        }

        $user = User::findByEmail($data['email']);
        if ($user && password_verify($data['password'], $user->password)) {
            Session::start();
            Session::set('user', [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url
            ]);

            $ipAddress = 'Unknown IP';

            if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
                $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($forwardedIps as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        $ipAddress = $ip;
                        break;
                    }
                }
            } elseif (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
                $ipAddress = $_SERVER['REMOTE_ADDR'];
            }

            $detect = new MobileDetect();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';
            $deviceType = $detect->isTablet() ? 'Tablet' : ($detect->isMobile() ? 'Mobile' : 'Desktop');

            $loginTime = date("Y-m-d H:i:s", time() + 60 * 60);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $_ENV['SMTP_Host'];
                $mail->SMTPAuth = $_ENV['SMTP_Auth'];
                $mail->Username = $_ENV['SMTP_Username'];
                $mail->Password = $_ENV['SMTP_Password'];
                $mail->SMTPSecure = $_ENV['SMTP_Secure'];
                $mail->Port = $_ENV['SMTP_Port'];

                $mail->setFrom($_ENV['SMTP_Username'], 'ZHOO');
                $mail->addAddress($user->email);
                $mail->isHTML(true);
                $mail->Subject = 'New Login Detected on Your Account';
                $mail->Body = '
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #121212;
                        color: #e0e0e0;
                        margin: 0;
                        padding: 20px;
                    }
                    .container {
                        background-color: #1e1e1e;
                        padding: 20px;
                        border-radius: 8px;
                        width: 90%;
                        max-width: 600px;
                        margin: auto;
                        border: 1px solid #333;
                    }
                    .header {
                        text-align: center;
                        font-size: 20px;
                        font-weight: bold;
                        color: #ffffff;
                        padding-bottom: 10px;
                        border-bottom: 1px solid #333;
                    }
                    .content {
                        padding: 20px;
                        font-size: 16px;
                        line-height: 1.6;
                    }
                    .footer {
                        text-align: center;
                        font-size: 14px;
                        color: #888;
                        margin-top: 20px;
                        border-top: 1px solid #333;
                        padding-top: 10px;
                    }
                    .btn {
                        display: inline-block;
                        background-color: yellow;
                        color: black;
                        padding: 10px 15px;
                        border-radius: 5px;
                        text-decoration: none;
                        font-weight: bold;
                    }
                    p {
                        color: #d4d4d4;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <img src="https://lh3.googleusercontent.com/pw/AP1GczMv2wyQdFNW7zVUC0W4vUVSOTl7yWWPf718uoa3c3Wlw2gCwANtImpdZcKPYmOoZ2ruqMcr4FHtFKm6sUoEC_9oBMG7wqOPDjH6arfHLkHJQd7EP-QA2BB9JL7KKRBl1r1_Lv7PMdxhJVN2svmC41ob=w500-h210-s-no-gm?authuser=0" width="150" alt="ZHOO Logo">
                    </div>
                    <p><strong>Dear ' . htmlspecialchars($user->name) . ',</strong></p>
                    <p>A new login to your account was detected.</p>
                    <p><strong>Time:</strong> ' . $loginTime . '</p>
                    <p><strong>IP Address:</strong> ' . htmlspecialchars($ipAddress) . '</p>
                    <p><strong>Device:</strong> ' . htmlspecialchars($deviceType) . ' (' . htmlspecialchars($userAgent) . ')</p>
                    <p>If this wasnt you, please reset your password immediately.</p>
                    <a class="btn" href="http://localhost/reset-password">Reset Password</a>
                    <div class="footer">
                        &copy; ' . date("Y") . ' ZHOO. All rights reserved.
                    </div>
                </div>
            </body>
            </html>';

                $mail->send();
            } catch (Exception $e) {
                error_log("Email notification failed: " . $mail->ErrorInfo);
            }

            return $this->redirect('/');
        } else {
            return $this->view('front/Login', [
                'error' => 'Invalid credentials, please try again.'
            ]);
        }
    }

    public function logout()
    {
        Session::destroy();
        header('Location: /');
    }

    public function googleLog()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->addScope("email");
        $client->addScope("profile");

        if (!isset($_GET['code'])) {
            header("Location: " . $client->createAuthUrl());
            exit();
        }

        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (isset($token['error'])) {
            die("Google authentication failed: " . $token['error_description']);
        }

        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account = $google_oauth->userinfo->get();

        $email = $google_account->email;
        $name = $google_account->name;
        $avatar = $google_account->picture;

        $userModel = new User();
        $user = $userModel->getUserByEmail($email);

        if (!$user) {
            $randomPassword = bin2hex(random_bytes(8));

            $userId = $userModel->createUser([
                'email' => $email,
                'name' => $name,
                'avatar_url' => $avatar,
                'role' => 'user',
                'password' => password_hash($randomPassword, PASSWORD_DEFAULT)
            ]);

            $user = $userModel->getUserById($userId);
        }

        if ($user && is_array($user)) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'avatar_url' => $user['avatar_url']
            ];
        }

        header("Location: /");
        exit();
    }


    public function githubLog()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $githubOAuthURL = 'https://github.com/login/oauth/authorize';
        $githubTokenURL = 'https://github.com/login/oauth/access_token';

        if (!isset($_GET['code'])) {
            $authURL = $githubOAuthURL . '?client_id=' . $_ENV['GITHUB_CLIENT_ID'] . '&redirect_uri=' . urlencode($_ENV['GITHUB_REDIRECT_URI']) . '&scope=user:email';
            header("Location: " . $authURL);
            exit();
        }

        $code = $_GET['code'];
        $tokenData = $this->getGitHubAccessToken($code);

        if (!$tokenData) {
            die("GitHub authentication failed.");
        }

        $user = $this->getGitHubUser($tokenData['access_token']);

        if (!$user) {
            die("Unable to fetch GitHub user data.");
        }

        if (empty($user['email'])) {
            die("Email not found in GitHub data.");
        }

        $userModel = new User();
        $existingUser = $userModel->getUserByEmail($user['email']);

        if (!$existingUser) {
            $userId = $userModel->createUser([
                'email' => $user['email'],
                'name' => $user['name'] ?? 'GitHub User',
                'avatar_url' => $user['avatar_url'] ?? 'default_avatar.png',
                'role' => 'user',
                'password' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT)
            ]);

            $existingUser = $userModel->getUserById($userId);
        }

        if ($existingUser && is_array($existingUser)) {
            $_SESSION['user'] = [
                'id' => $existingUser['id'],
                'name' => $existingUser['name'],
                'email' => $existingUser['email'],
                'avatar_url' => $existingUser['avatar_url']
            ];
        }

        header("Location: /");
        exit();
    }




    private function getGitHubAccessToken($code)
    {
        $url = 'https://github.com/login/oauth/access_token';
        $data = [
            'client_id' => $_ENV['GITHUB_CLIENT_ID'],
            'client_secret' => $_ENV['GITHUB_CLIENT_SECRET'],
            'code' => $code,
            'redirect_uri' => $_ENV['GITHUB_REDIRECT_URI'],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);

        return isset($responseData['access_token']) ? $responseData : null;
    }

    private function getGitHubUser($accessToken)
    {
        $url = 'https://api.github.com/user';
        $headers = [
            'Authorization: token ' . $accessToken,
            'User-Agent: Zhoo'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        $userData = json_decode($response, true);

        if (!isset($userData['id'])) {
            return null;
        }

        $emailsUrl = 'https://api.github.com/user/emails';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $emailsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $emailsResponse = curl_exec($ch);
        curl_close($ch);

        $emailsData = json_decode($emailsResponse, true);

        $primaryEmail = '';
        foreach ($emailsData as $email) {
            if ($email['primary'] && $email['verified']) {
                $primaryEmail = $email['email'];
                break;
            }
        }

        return [
            'email' => $primaryEmail,
            'name' => $userData['name'],
            'avatar_url' => $userData['avatar_url']
        ];
    }

}