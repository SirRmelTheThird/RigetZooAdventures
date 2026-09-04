<?php

namespace Core;

class Validator
{
    private $errors = [];

    public function required($field, $value, $message = null)
    {
        if (empty(trim($value))) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' is required';
        }
        return $this;
    }

    public function email($field, $value, $message = null)
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? 'Invalid email address';
        }
        return $this;
    }

    public function integer($field, $value, $min = null, $max = null, $message = null)
    {
        if (!empty($value)) {
            if (!is_numeric($value) || intval($value) != $value) {
                $this->errors[$field] = $message ?? ucfirst($field) . ' must be an integer';
            } elseif ($min !== null && $value < $min) {
                $this->errors[$field] = $message ?? ucfirst($field) . " must be at least {$min}";
            } elseif ($max !== null && $value > $max) {
                $this->errors[$field] = $message ?? ucfirst($field) . " must be at most {$max}";
            }
        }
        return $this;
    }

    public function date($field, $value, $message = null)
    {
        if (!empty($value)) {
            $date = \DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $this->errors[$field] = $message ?? 'Invalid date format';
            }
        }
        return $this;
    }

    public function futureDate($field, $value, $message = null)
    {
        if (!empty($value)) {
            $date = \DateTime::createFromFormat('Y-m-d', $value);
            $today = new \DateTime('today');

            if ($date && $date < $today) {
                $this->errors[$field] = $message ?? 'Date must be in the future';
            }
        }
        return $this;
    }

    public function minLength($field, $value, $length, $message = null)
    {
        if (!empty($value) && strlen($value) < $length) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be at least {$length} characters";
        }
        return $this;
    }

    public function matches($field, $value, $matchValue, $matchField, $message = null)
    {
        if ($value !== $matchValue) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must match {$matchField}";
        }
        return $this;
    }

    public static function sanitize($value)
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    public function passes()
    {
        return empty($this->errors);
    }

    public function fails()
    {
        return !$this->passes();
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getFirstError()
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
}
