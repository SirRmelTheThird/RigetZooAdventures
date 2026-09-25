<?php

declare(strict_types=1);

namespace Core\View;

final readonly class OldInput
{
    private function __construct(private array $values)
    {
    }

    public static function fromFlash(mixed $flash): self
    {
        if (!is_array($flash)) {
            return new self([]);
        }

        return new self($flash);
    }

    public function get(string $field): string
    {
        if (!array_key_exists($field, $this->values)) {
            return '';
        }

        return (string) $this->values[$field];
    }
}
