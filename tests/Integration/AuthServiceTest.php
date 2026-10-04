<?php

declare(strict_types=1);

namespace Tests\Integration;

use Core\Logging\Logger;
use Core\Session\PhpSessionStore;
use DTOs\Auth\LoginCredentials;
use DTOs\Auth\Registration;
use Models\Auth\Customer;
use PHPUnit\Framework\TestCase;
use Repositories\Contracts\Auth\CustomerRepository;
use Services\Auth\AuthService;
use Tests\Support\MemoryLogWriter;

final class AuthServiceTest extends TestCase
{
    public function testAuthenticateAndRegisterUseCustomerRepository(): void
    {
        $customer = new Customer();
        $customer->setRawAttributes([
            'id' => 'customer-1',
            'username' => 'ada',
            'password' => password_hash('secret', PASSWORD_BCRYPT),
        ]);
        $created = [];
        $repository = new class ($customer, $created) implements CustomerRepository {
            public function __construct(
                private Customer $customer,
                private array &$created,
            ) {
            }

            public function findByUsername(string $username): ?Customer
            {
                return $username === $this->customer->username ? $this->customer : null;
            }

            public function saveStripeCustomerId(Customer $customer, string $stripeCustomerId): void
            {
                $customer->stripe_customer_id = $stripeCustomerId;
            }

            public function findById(string $customerId): ?Customer
            {
                return $customerId === (string) $this->customer->id ? $this->customer : null;
            }

            public function usernameExists(string $username): bool
            {
                return $username === $this->customer->username;
            }

            public function emailExists(string $email): bool
            {
                return false;
            }

            public function create(array $data): Customer
            {
                $this->created[] = $data;

                return $this->customer;
            }

            public function findWithRewardPoints(string $customerId): ?Customer
            {
                return $customerId === (string) $this->customer->id ? $this->customer : null;
            }
        };
        $auth = new AuthService(
            new Logger(new MemoryLogWriter()),
            new PhpSessionStore(),
            $repository,
        );

        self::assertSame($customer, $auth->authenticate(new LoginCredentials('ada', 'secret')));

        $registration = new Registration('Grace', 'Hopper', 'grace', 'grace@example.test', 'secret');
        $auth->register($registration);

        self::assertSame('grace', $created[0]['username']);
        self::assertSame($customer, $auth->findAuthenticatedCustomer('customer-1'));
    }
}
