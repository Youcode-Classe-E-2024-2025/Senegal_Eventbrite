<?php

namespace Controller_front;

use Core\Controller;
use Core\View;
use Core\Auth;
use Core\Validator;
use Model\User;


class AuthController extends Controller
{
    public function signup()
    {
        

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $validator = new Validator($_POST, [
                'username' => ['required', 'min:3', 'max:20', 'unique:users,username'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'confirmed', 'min:6'],
                'password_confirmation' => ['required']
            ]);

            if ($validator->fails()) {
                return $this->view('front/Signup', [
                    'errors' => $validator->errors(),
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
            $validator = new Validator($_POST, [
                'email' => ['required', 'email'],
                'password' => ['required', 'min:6']
            ]);

            if ($validator->fails()) {
                return $this->view('front/Login', [
                    'errors' => $validator->errors(),
                    'data' => $_POST
                ]);
            }

            $user = User::where('email', $data['email'])->first();

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
        if (Auth::check()) {
            return $this->redirect('/');
        }

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
}