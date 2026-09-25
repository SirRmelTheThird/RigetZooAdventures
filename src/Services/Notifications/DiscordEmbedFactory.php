<?php

declare(strict_types=1);

namespace Services\Notifications;

use Support\Messages;

final class DiscordEmbedFactory
{
    private const SUCCESS_COLOR = 5763719;
    private const FAILURE_COLOR = 15548997;

    private const FOOTER_TEXT = 'Riget Zoo Adventures';

    public function paymentSucceeded(string $paymentIntentId, int $amountMinorUnits): array
    {
        return $this->create(
            '✅ Payment Succeeded',
            self::SUCCESS_COLOR,
            [
                [
                    'name' => 'Amount',
                    'value' => $this->formatAmount($amountMinorUnits),
                    'inline' => true,
                ],
                [
                    'name' => 'Status',
                    'value' => 'Paid',
                    'inline' => true,
                ],
                [
                    'name' => 'Payment Intent',
                    'value' => $this->formatPaymentIntent($paymentIntentId),
                    'inline' => false,
                ],
            ]
        );
    }

    public function paymentFailed(string $paymentIntentId, ?string $failureMessage, int $amountMinorUnits): array
    {
        return $this->create(
            '❌ Payment Failed',
            self::FAILURE_COLOR,
            [
                [
                    'name' => 'Amount',
                    'value' => $this->formatAmount($amountMinorUnits),
                    'inline' => true,
                ],
                [
                    'name' => 'Status',
                    'value' => 'Failed',
                    'inline' => true,
                ],
                [
                    'name' => 'Payment Intent',
                    'value' => $this->formatPaymentIntent($paymentIntentId),
                    'inline' => false,
                ],
                [
                    'name' => 'Failure Reason',
                    'value' => $this->formatFailureMessage($failureMessage),
                    'inline' => false,
                ],
            ]
        );
    }

    private function create(string $title, int $color, array $fields): array
    {
        return [
            'embeds' => [
                [
                    'title' => $title,
                    'color' => $color,
                    'fields' => $fields,
                    'footer' => [
                        'text' => self::FOOTER_TEXT,
                    ],
                    'timestamp' => gmdate('c'),
                ],
            ],
        ];
    }

    private function formatAmount(int $amountMinorUnits): string
    {
        return '£' . number_format(
            $amountMinorUnits / 100,
            2,
            '.',
            ','
        );
    }

    private function formatPaymentIntent(string $paymentIntentId): string
    {
        return '`' . $paymentIntentId . '`';
    }

    private function formatFailureMessage(?string $failureMessage): string
    {
        $message = trim($failureMessage ?? '');

        if ($message === '') {
            return Messages::DISCORD_NO_FAILURE_REASON;
        }

        return mb_substr($message, 0, 1000);
    }
}
