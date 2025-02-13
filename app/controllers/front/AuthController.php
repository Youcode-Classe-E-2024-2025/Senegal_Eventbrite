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

            header('Location: /');
            exit();
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
                'name' => $user['name'],
                'avatar_url' => $user['avatar_url'],
                'role' => 'user',
                'password' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT) // Generate password
            ]);
            $existingUser = $userModel->getUserById($userId);
        }

        Session::start();
        Session::set('user', [
            'id' => $existingUser->id,
            'name' => $user['name'],
            'email' => $user['email'],
            'avatar_url' => $user['avatar_url']
        ]);

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