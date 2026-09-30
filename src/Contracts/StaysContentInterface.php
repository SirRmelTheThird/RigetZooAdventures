<?php

declare(strict_types=1);

namespace Contracts;

interface StaysContentInterface
{
    public function getHeader(): array;
    public function getEmpty(): array;
    public function getCard(): array;
}
