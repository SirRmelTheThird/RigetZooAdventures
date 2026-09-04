<?php

namespace Requests;

use Core\Validator;
use Core\Session;

abstract class FormRequest
{
    protected $data;
    protected $errors = [];
    protected $validator;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->validator = new Validator();
    }

    abstract protected function rules();

    public function validate(): bool
    {
        $this->rules();
        $this->errors = $this->validator->getErrors();

        return $this->validator->passes();
    }

    public function validated(): array
    {
        $validated = [];

        foreach ($this->data as $key => $value) {
            if (!is_array($value)) {
                $validated[$key] = Validator::sanitize($value);
            } else {
                $validated[$key] = $value;
            }
        }

        return $validated;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return !$this->validate();
    }

    public function failWithRedirect()
    {
        Session::flash('errors', $this->errors());
        Session::flash('form_data', $this->data);
    }

    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
}
