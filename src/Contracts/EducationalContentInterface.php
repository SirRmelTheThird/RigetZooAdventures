<?php

declare(strict_types=1);

namespace Contracts;

interface EducationalContentInterface
{
    public function getHeader(): array;
    public function getFeature(): array;
    public function getTopics(): array;
}