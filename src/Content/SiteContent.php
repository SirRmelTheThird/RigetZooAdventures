<?php

declare(strict_types=1);

namespace Content;

use Contracts\SiteContentInterface;

final class SiteContent implements SiteContentInterface
{
    public function __construct(
        private readonly array $data
    ) {}

    public function getName(): string
    {
        return $this->data['name'] ?? '';
    }

    public function getDescription(): string
    {
        return $this->data['description'] ?? '';
    }

    public function getLogo(): string
    {
        return $this->data['logo'] ?? '';
    }

    public function getNav(): array
    {
        return $this->data['nav'];
    }

    public function getTagline(): string
    {
        return $this->data['tagline'] ?? '';
    }

    public function getSocial(): array
    {
        return $this->data['social'] ?? [];
    }
}