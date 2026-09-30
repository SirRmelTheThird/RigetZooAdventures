<?php

declare(strict_types=1);

namespace Contracts;

interface AttractionsContentInterface
{
    public function getHeader(): array;
    public function getFeatures(): array;
    public function getClosing(): array;
}
