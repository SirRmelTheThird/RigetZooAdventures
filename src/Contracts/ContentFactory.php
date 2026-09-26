<?php

declare(strict_types=1);

namespace App\Factories;

use App\Models\AuthContentInterface;
use App\Models\AuthContent;
use App\Models\CheckoutContentInterface;
use App\Models\CheckoutContent;
use App\Models\HomeContentInterface;
use App\Models\HomeContent;
use App\Models\ProfileContentInterface;
use App\Models\ProfileContent;
use App\Models\TicketsContentInterface;
use App\Models\TicketsContent;
use App\Models\CartContentInterface;
use App\Models\CartContent;

final class ContentFactory
{
    private const MAP = [
        AuthContentInterface::class     => AuthContent::class,
        CheckoutContentInterface::class => CheckoutContent::class,
        CartContentInterface::class     => CartContent::class,
        HomeContentInterface::class     => HomeContent::class,
        ProfileContentInterface::class  => ProfileContent::class,
        TicketsContentInterface::class  => TicketsContent::class,
    ];

    private const FILE_MAP = [
        AuthContentInterface::class     => 'auth',
        CheckoutContentInterface::class => 'checkout',
        CartContentInterface::class     => 'cart',
        HomeContentInterface::class     => 'home',
        ProfileContentInterface::class  => 'profile',
        TicketsContentInterface::class  => 'tickets',
    ];

    public static function fileFor(string $interface): string
    {
        if (!isset(self::FILE_MAP[$interface])) {
            throw new \RuntimeException(
                "No content file mapped for: {$interface}"
            );
        }

        return self::FILE_MAP[$interface];
    }

    public static function create(string $interface, array $data): object
    {
        if (!isset(self::MAP[$interface])) {
            throw new \RuntimeException(
                "No object wrapper mapped for content: {$interface}"
            );
        }

        $className = self::MAP[$interface];

        if (!is_subclass_of($className, $interface)) {
            throw new \RuntimeException(
                "Class {$className} does not implement {$interface}"
            );
        }

        return new $className($data);
    }
}