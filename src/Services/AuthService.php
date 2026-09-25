<?php

declare(strict_types=1);

namespace Services;

use Core\Logging\Logger;
use DTOs\LoginCredentials;
use DTOs\Registration;
use Exceptions\AuthException;
use Exceptions\ValidationException;
use Models\Customer;
use Support\Messages;

final class AuthService
{
    public function __construct(private readonly Logger $logger)
    {
    }

    public function authenticate(LoginCredentials $credentials): Customer
    {
        $customer = Customer::where('username', $credentials->username)->first();

        if ($customer === null || !password_verify($credentials->password, $customer->password)) {
            $this->logger->warning('Failed login', ['username' => $credentials->username]);

            throw AuthException::invalidCredentials();
        }

        $this->logger->info('Customer logged in', ['customer_id' => $customer->id]);

        return $customer;
    }

    public function register(Registration $registration): Customer
    {
        $errors = [];

        if (Customer::where('username', $registration->username)->exists()) {
            $errors['username'] = Messages::USERNAME_TAKEN;
        }

        if (Customer::where('email', $registration->email)->exists()) {
            $errors['email'] = Messages::EMAIL_TAKEN;
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $customer = Customer::create([
            'first_name' => $registration->firstName,
            'last_name' => $registration->lastName,
            'username' => $registration->username,
            'email' => $registration->email,
            'password' => $registration->password,
        ]);

        $this->logger->info('Customer registered', ['customer_id' => $customer->id]);

        return $customer;
    }
}
