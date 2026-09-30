<?php

declare(strict_types=1);

namespace Contracts;

interface ErrorsContentInterface
{
    public function getServerError(): array;
    public function getNotFound(): array;
    public function getHome(): array;
}
