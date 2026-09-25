<?php

declare(strict_types=1);

namespace Core\Validation;

use DateTimeImmutable;

final class RuleDefinition
{
    private const PARAMETER_SEPARATOR = ':';
    private const DATE_FORMAT = 'Y-m-d';

    private function __construct(
        public readonly Rule $rule,
        public readonly ?int $parameter,
    ) {
    }

    public static function parse(string $raw): self
    {
        $parts = explode(self::PARAMETER_SEPARATOR, $raw, 2);
        $rule = Rule::tryFrom($parts[0]);

        if ($rule === null) {
            throw InvalidRuleException::unknown($parts[0]);
        }

        $hasParameter = count($parts) === 2;

        if (!$rule->requiresParameter()) {
            if ($hasParameter) {
                throw InvalidRuleException::unexpectedParameter($rule);
            }

            return new self($rule, null);
        }

        if (!$hasParameter) {
            throw InvalidRuleException::missingParameter($rule);
        }

        $parameter = filter_var($parts[1], FILTER_VALIDATE_INT);

        if ($parameter === false) {
            throw InvalidRuleException::invalidParameter($rule, $parts[1]);
        }

        return new self($rule, $parameter);
    }

    public function passes(mixed $value): bool
    {
        return match ($this->rule) {
            Rule::Required => is_scalar($value) && trim((string) $value) !== '',
            Rule::Email => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            Rule::Integer => filter_var($value, FILTER_VALIDATE_INT) !== false,
            Rule::Min => is_numeric($value) && (float) $value >= $this->parameter,
            Rule::Max => is_numeric($value) && (float) $value <= $this->parameter,
            Rule::MinLength => is_string($value) && mb_strlen($value) >= $this->parameter,
            Rule::Date => $this->parseDate($value) !== null,
            Rule::FutureDate => $this->isTodayOrLater($value),
        };
    }

    private function isTodayOrLater(mixed $value): bool
    {
        $date = $this->parseDate($value);

        if ($date === null) {
            return false;
        }

        return $date >= new DateTimeImmutable('today');
    }

    private function parseDate(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value)) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!' . self::DATE_FORMAT, $value);

        if ($date === false) {
            return null;
        }

        if ($date->format(self::DATE_FORMAT) !== $value) {
            return null;
        }

        return $date;
    }
}
