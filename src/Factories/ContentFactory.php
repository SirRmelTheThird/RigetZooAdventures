<?php

declare(strict_types=1);

namespace Factories;

use Content\AuthContent;
use Content\CartContent;
use Content\CheckoutContent;
use Content\HomeContent;
use Content\ProfileContent;
use Content\TicketsContent;
use Content\SiteContent;
use Content\TermsContent;
use Content\ErrorsContent;
use Content\StaysContent;
use Content\EducationalContent;
use Content\AttractionsContent;
use Contracts\AuthContentInterface;
use Contracts\CartContentInterface;
use Contracts\CheckoutContentInterface;
use Contracts\HomeContentInterface;
use Contracts\ProfileContentInterface;
use Contracts\TicketsContentInterface;
use Contracts\SiteContentInterface;
use Contracts\TermsContentInterface;
use Contracts\ErrorsContentInterface;
use Contracts\StaysContentInterface;
use Contracts\EducationalContentInterface;
use Contracts\AttractionsContentInterface;
use RuntimeException;

final class ContentFactory
{
    private const MAP = [
        AuthContentInterface::class => AuthContent::class,
        CheckoutContentInterface::class => CheckoutContent::class,
        CartContentInterface::class => CartContent::class,
        HomeContentInterface::class => HomeContent::class,
        ProfileContentInterface::class => ProfileContent::class,
        TicketsContentInterface::class => TicketsContent::class,
        SiteContentInterface::class => SiteContent::class,
        TermsContentInterface::class => TermsContent::class,
        ErrorsContentInterface::class => ErrorsContent::class,
        StaysContentInterface::class => StaysContent::class,
        EducationalContentInterface::class => EducationalContent::class,
        AttractionsContentInterface::class => AttractionsContent::class,
    ];

    private const FILE_MAP = [
        AuthContentInterface::class => 'auth',
        CheckoutContentInterface::class => 'checkout',
        CartContentInterface::class => 'cart',
        HomeContentInterface::class => 'home',
        ProfileContentInterface::class => 'profile',
        TicketsContentInterface::class => 'tickets',
        SiteContentInterface::class => 'site',
        TermsContentInterface::class => 'terms',
        ErrorsContentInterface::class => 'errors',
        StaysContentInterface::class => 'stays',
        EducationalContentInterface::class => 'educational',
        AttractionsContentInterface::class => 'attractions',
    ];

    public static function fileFor(string $interface): string
    {
        if (!isset(self::FILE_MAP[$interface])) {
            throw new RuntimeException(
                "No content file mapped for: {$interface}"
            );
        }

        return self::FILE_MAP[$interface];
    }

    public static function create(string $interface, array $data): object
    {
        if (!isset(self::MAP[$interface])) {
            throw new RuntimeException(
                "No object wrapper mapped for content: {$interface}"
            );
        }

        $className = self::MAP[$interface];

        if (!is_subclass_of($className, $interface)) {
            throw new RuntimeException(
                "Class {$className} does not implement {$interface}"
            );
        }

        return new $className($data);
    }
}
