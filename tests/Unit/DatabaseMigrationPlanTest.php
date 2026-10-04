<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class DatabaseMigrationPlanTest extends TestCase
{
    public function testMigrationPlanIncludesProcessedWebhookEventsTable(): void
    {
        $migrationPlan = file_get_contents(dirname(__DIR__, 2) . '/database/migrate.php');

        self::assertIsString($migrationPlan);
        self::assertStringContainsString("'CreateProcessedWebhookEventsTable'", $migrationPlan);
    }
}
