<?php

declare(strict_types=1);

namespace Contracts;

interface SiteContentInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function getNav(): array;
    public function getLogo(): string;
    public function getTagline(): string;
    public function getSocial(): array;
}