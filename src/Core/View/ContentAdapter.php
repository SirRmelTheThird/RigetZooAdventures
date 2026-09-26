<?php

declare(strict_types=1);

namespace Core\View;

final class ContentAdapter
{
    public static function resolve(string $interfaceClass): string
    {
        $map = [
            \App\Models\HomeContentInterface::class => 'home',
            \App\Models\AuthContentInterface::class => 'auth',
            \App\Models\CheckoutContentInterface::class => 'checkout',
            \App\Models\ProfileContentInterface::class => 'profile',
            \App\Models\TicketsContentInterface::class => 'tickets',
        ];
        if (!array_key_exists($interfaceClass, $map)) {
            throw new ViewException("Unknown content interface: {$interfaceClass}");
        }
        return $map[$interfaceClass];
    }
}