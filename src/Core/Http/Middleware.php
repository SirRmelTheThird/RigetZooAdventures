<?php

declare(strict_types=1);

namespace Core\Http;

interface Middleware
{
    public function handle(Request $request): ?Response;
}
