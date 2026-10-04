<?php

declare(strict_types=1);

namespace Repositories\Eloquent\Auth;

use Models\Auth\Customer;
use Repositories\Contracts\Auth\CustomerRepository;

final class EloquentCustomerRepository implements CustomerRepository
{
    public function findById(string $customerId): ?Customer
    {
        return Customer::find($customerId);
    }

    public function saveStripeCustomerId(Customer $customer, string $stripeCustomerId): void
    {
        $customer->setAttribute('stripe_customer_id', $stripeCustomerId);
        $customer->save();
    }

    public function findByUsername(string $username): ?Customer
    {
        return Customer::where('username', $username)->first();
    }

    public function usernameExists(string $username): bool
    {
        return Customer::where('username', $username)->exists();
    }

    public function emailExists(string $email): bool
    {
        return Customer::where('email', $email)->exists();
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function findWithRewardPoints(string $customerId): ?Customer
    {
        return Customer::with('rewardPoints')->find($customerId);
    }
}
