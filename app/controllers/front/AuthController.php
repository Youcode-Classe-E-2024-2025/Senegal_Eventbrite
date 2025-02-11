<?php

namespace Controller_front;

use Core\Controller;
use Core\View;
use Core\Auth;
use Core\Validator;
use Model\User;
use Core\Session;
use Exception; // Add this to handle exceptions

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
            $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
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
        $user->password = password_hash($data['password'], PASSWORD_DEFAULT);

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
            Session::set('user', $user);

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
}