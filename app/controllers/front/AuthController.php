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

        Auth::login($user);
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
            // Start session
            Session::start();
            Session::set('user', [
                'id' => $user->id,
                'name' => $user->name,
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
            $userId = $userModel->createUser([
                'email' => $email,
                'name' => $name,
                'avatar_url' => $avatar,
                'role' => 'user',
                'password' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT)
            ]);
            $user = $userModel->getUserById($userId);
        }

        Session::start();
        Session::set('user', [
            'id' => $user->id,
            'name' => $name,
            'email' => $email,
            'avatar_url' => $avatar
        ]);

        header("Location: /");
        exit();
    }
}