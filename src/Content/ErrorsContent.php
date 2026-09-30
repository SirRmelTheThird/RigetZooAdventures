<?php

declare(strict_types=1);

namespace Content;

use Contracts\ErrorsContentInterface;

final class ErrorsContent implements ErrorsContentInterface
{
    public function __construct(
        private readonly array $data
    ) {
    }

    public function getServerError(): array
    {
        return $this->data['server_error'] ?? [];
    }

    public function getNotFound(): array
    {
        return $this->data['not_found'] ?? [];
    }

    public function getHome(): array
    {
        return $this->data['home'] ?? [];
    }
}
