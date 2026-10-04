<?php

declare(strict_types=1);

namespace Repositories\Contracts\Auth;

use Models\Auth\Customer;

interface CustomerRepository
{
    public function findById(string $customerId): ?Customer;

    public function saveStripeCustomerId(Customer $customer, string $stripeCustomerId): void;

    public function findByUsername(string $username): ?Customer;

    public function usernameExists(string $username): bool;

    public function emailExists(string $email): bool;

    /** @param array<string, mixed> $data */
    public function create(array $data): Customer;

    public function findWithRewardPoints(string $customerId): ?Customer;
}
