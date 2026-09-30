<?php

declare(strict_types=1);

namespace Core\View;

use Exceptions\Views\ViewException;

final class Content
{
    public function __construct(private readonly array $data)
    {
    }

    public function get(string $key): mixed
    {
        if (!isset($this->data[$key])) {
            throw ViewException::missingContentKey($key);
        }

        return $this->data[$key];
    }
}
