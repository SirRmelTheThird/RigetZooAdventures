<?php

namespace Services;

use Models\Customer;
use Core\Logger;
use Core\Session;

class AuthService
{
    public function login($username, $password): bool
    {
        $customer = Customer::findByUsername($username);

        if (!$customer) {
            Logger::warning('Login attempt with invalid username', ['username' => $username]);
            return false;
        }

        if (!$customer->verifyPassword($password)) {
            Logger::warning('Login attempt with invalid password', ['username' => $username]);
            return false;
        }

        // Regenerate session ID for security
        Session::regenerate();

        // Set session data
        Session::set('customer_id', $customer->id);
        Session::set('username', $customer->username);
        Session::set('first_name', $customer->first_name);
        Session::set('email', $customer->email);

        Logger::info('User logged in', [
            'customer_id' => $customer->id,
            'username' => $customer->username
        ]);

        return true;
    }

    public function register(array $data): bool
    {
        if (Customer::usernameExists($data['username'])) {
            Logger::warning('Registration attempt with existing username', ['username' => $data['username']]);
            return false;
        }

        if (Customer::emailExists($data['email'])) {
            Logger::warning('Registration attempt with existing email', ['email' => $data['email']]);
            return false;
        }

        try {
            $customer = Customer::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password']
            ]);

            Logger::info('New user registered', [
                'customer_id' => $customer->id,
                'username' => $customer->username,
                'email' => $customer->email
            ]);

            return true;
        } catch (\Exception $e) {
            Logger::exception($e, ['data' => $data]);
            return false;
        }
    }

    public function logout()
    {
        $username = Session::get('username');

        Session::destroy();

        Logger::info('User logged out', ['username' => $username]);
    }

    public function check(): bool
    {
        return Session::isLoggedIn();
    }

    public function user()
    {
        if (!$this->check()) {
            return null;
        }

        return Customer::find(Session::getUserId());
    }
}