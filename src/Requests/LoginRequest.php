<?php

namespace Requests;

class LoginRequest extends FormRequest
{
    protected function rules()
    {
        $this->validator
            ->required('username', $this->get('username'))
            ->required('password', $this->get('password'))
            ->minLength('password', $this->get('password'), 6);
    }

    public function credentials(): array
    {
        return [
            'username' => $this->get('username'),
            'password' => $this->get('password')
        ];
    }
}
