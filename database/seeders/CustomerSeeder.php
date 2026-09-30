<?php

namespace Database\Seeders;

use Database\Seeders\Support\JsonFileLoader;
use Models\Auth\Customer;

class CustomerSeeder
{
    public function run(): void
    {
        $users = (new JsonFileLoader(__DIR__))->load('/JSON/users.json');

        foreach ($users as $userData) {
            Customer::create([
                'first_name' => $userData['first_name'],
                'last_name'  => $userData['last_name'],
                'username'   => $userData['username'],
                'email'      => $userData['email'],
                'id'         => (string) uniqid(),
                'password'   => $userData['password'],
            ]);
        }

        echo "Seeded: customers (" . count($users) . " accounts)\n";
    }
}