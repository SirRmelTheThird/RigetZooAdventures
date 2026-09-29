<?php

declare(strict_types=1);

namespace Illuminate\Database\Eloquent;

abstract class Model
{
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->setRawAttributes($attributes);
    }

    public function setRawAttributes(array $attributes): void
    {
        $this->attributes = $attributes;

        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }
}
