<?php

namespace DTOs;

class CreateUserDTO
{
    public string $firstName;
    public string $lastName;
    public string $username;
    public string $email;
    public string $password;

    public function __construct(array $data)
    {
        $this->firstName = $data['first_name'] ?? '';
        $this->lastName = $data['last_name'] ?? '';
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password
        ];
    }
}
