<?php

namespace Controllers;

use Core\Response;
use Core\Session;
use Services\AuthService;
use Requests\LoginRequest;
use Requests\SignupRequest;
use DTOs\CreateUserDTO;
use Core\Logger;

class Auth extends Controller
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin()
    {
        Response::view('auth/login');
    }

    public function login()
    {
        $request = new LoginRequest($_POST);

        if (!$request->validate()) {
            $request->failWithRedirect();
            Response::redirect('/login');
        }

        $credentials = $request->credentials();

        if ($this->authService->login($credentials['username'], $credentials['password'])) {
            Session::flash('success', 'Logged in successfully!');
            Response::redirect('/');
        }

        Session::flash('error', 'Invalid username or password');
        Response::redirect('/login');
    }

    public function showSignup()
    {
        Response::view('auth/signup');
    }

    public function signup()
    {
        $request = new SignupRequest($_POST);

        if (!$request->validate()) {
            $request->failWithRedirect();
            Response::redirect('/signup');
        }

        $dto = new CreateUserDTO($request->validated());

        if ($this->authService->register($dto->toArray())) {
            Session::flash('success', 'Account created! Please log in.');
            Response::redirect('/login');
        }

        Session::flash('error', 'Failed to create account. Please try again.');
        Response::redirect('/signup');
    }

    public function logout()
    {
        $this->authService->logout();
        Session::flash('success', 'You have been logged out');
        Response::redirect('/');
    }
}
