<?php

declare(strict_types=1);

namespace Core\Validation;

final class Validator
{
    public function validate(array $data, array $rules): ValidationResult
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $error = $this->firstError($field, $data, $fieldRules);

            if ($error === null) {
                continue;
            }

            $errors[$field] = $error;
        }

        return new ValidationResult($errors);
    }

    private function firstError(string $field, array $data, array $fieldRules): ?string
    {
        foreach ($fieldRules as $raw) {
            $definition = RuleDefinition::parse($raw);

            if ($this->satisfies($definition, $field, $data)) {
                continue;
            }

            return strtr($definition->rule->message(), [
                ':field' => $this->label($field),
                ':param' => (string) $definition->parameter,
            ]);
        }

        return null;
    }

    private function satisfies(RuleDefinition $definition, string $field, array $data): bool
    {
        if ($this->isBlank($field, $data)) {
            return $definition->rule !== Rule::Required;
        }

        return $definition->passes($data[$field]);
    }

    private function isBlank(string $field, array $data): bool
    {
        if (!array_key_exists($field, $data)) {
            return true;
        }

        return $data[$field] === null || $data[$field] === '';
    }

    private function label(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }
}
