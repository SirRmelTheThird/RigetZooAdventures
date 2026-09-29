<?php

declare(strict_types=1);

namespace Services\Auth;

use Core\Logging\Logger;
use Core\Session\SessionStore;
use DTOs\Auth\LoginCredentials;
use DTOs\Auth\Registration;
use Exceptions\Auth\AuthException;
use Exceptions\Validation\ValidationException;
use Models\Auth\Customer;
use Repositories\Contracts\Auth\CustomerRepository;
use Support\Messages;

final class AuthService
{
    public function __construct(
        private readonly Logger $logger,
        private readonly SessionStore $session,
        private readonly CustomerRepository $customers,
    ) {
    }

    public function authenticate(LoginCredentials $credentials): Customer
    {
        $customer = $this->customers->findByUsername($credentials->username);

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

        if ($this->customers->usernameExists($registration->username)) {
            $errors['username'] = Messages::USERNAME_TAKEN;
        }

        if ($this->customers->emailExists($registration->email)) {
            $errors['email'] = Messages::EMAIL_TAKEN;
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $customer = $this->customers->create([
            'first_name' => $registration->firstName,
            'last_name' => $registration->lastName,
            'username' => $registration->username,
            'email' => $registration->email,
            'password' => $registration->password, // setPasswordAttribute mutator handles hashing
        ]);

        $this->logger->info('Customer registered', ['customer_id' => $customer->id]);

        return $customer;
    }

    public function findAuthenticatedCustomer(string $customerId): ?Customer
    {
        return $this->customers->findWithRewardPoints($customerId);
    }

    public function invalidateSession(): void
    {
        $this->session->invalidate();
    }
}
