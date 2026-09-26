<?php

declare(strict_types=1);

namespace App\Models;

interface HomeContentInterface
{
    public function getHero(): array;
    public function getFacts(): array;
    public function getVisit(): array;
}
