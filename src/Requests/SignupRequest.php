<?php

namespace Requests;

class SignupRequest extends FormRequest
{
    protected function rules()
    {
        $this->validator
            ->required('first_name', $this->get('first_name'))
            ->required('last_name', $this->get('last_name'))
            ->required('username', $this->get('username'))
            ->minLength('username', $this->get('username'), 3)
            ->required('email', $this->get('email'))
            ->email('email', $this->get('email'))
            ->required('password', $this->get('password'))
            ->minLength('password', $this->get('password'), 6)
            ->matches('password', $this->get('password'), $this->get('confirm_password'), 'Confirm Password');
    }
}
