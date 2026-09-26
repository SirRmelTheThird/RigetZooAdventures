<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Services\Notifications\DiscordEmbedFactory;
use Support\Messages;

final class DiscordEmbedFactoryTest extends TestCase
{
    public function testPaymentSucceededEmbedHasCorrectTitleColorAndFields(): void
    {
        $factory = new DiscordEmbedFactory();

        $embed = $factory->paymentSucceeded('pi_123', 1999);

        self::assertArrayHasKey('embeds', $embed);
        self::assertCount(1, $embed['embeds']);

        $first = $embed['embeds'][0];
        self::assertSame('✅ Payment Succeeded', $first['title']);

        $fieldMap = [];
        foreach ($first['fields'] as $field) {
            $fieldMap[$field['name']] = $field['value'];
        }

        self::assertSame('£19.99', $fieldMap['Amount']);
        self::assertSame('Paid', $fieldMap['Status']);
        self::assertSame('`pi_123`', $fieldMap['Payment Intent']);
    }

    public function testPaymentFailedWithNullFailureReasonUsesDefaultMessage(): void
    {
        $factory = new DiscordEmbedFactory();

        $embed = $factory->paymentFailed('pi_123', null, 100);

        $first = $embed['embeds'][0];

        $failureReason = null;
        foreach ($first['fields'] as $field) {
            if ($field['name'] === 'Failure Reason') {
                $failureReason = $field['value'];
            }
        }

        self::assertSame(Messages::DISCORD_NO_FAILURE_REASON, $failureReason);
    }
}
