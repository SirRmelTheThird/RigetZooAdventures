<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Enums\ItemType;
use Illuminate\Database\Eloquent\Collection;
use Models\OrderItem;
use PHPUnit\Framework\TestCase;
use Services\OrderItemLoader;

final class OrderItemLoaderTest extends TestCase
{
    public function testLoadHandlesEmptyCollectionGracefully(): void
    {
        $loader = new OrderItemLoader();
        $items = new Collection();

        $loader->load($items);

        self::assertTrue($items->isEmpty());
    }
}
