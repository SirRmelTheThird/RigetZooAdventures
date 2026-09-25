<?php

declare(strict_types=1);

namespace Core\View;

use DateTimeImmutable;
use DateTimeInterface;

final class Format
{
    private const CURRENCY_SYMBOL = '£';
    private const MONEY_DECIMALS = 2;
    private const DATE_DISPLAY = 'M j, Y';
    private const DATE_ISO = 'Y-m-d';
    private const NOT_AVAILABLE = 'N/A';

    public static function e(string|int|float|null $value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function when(bool $condition, string $output): string
    {
        if ($condition) {
            return $output;
        }

        return '';
    }

    public static function money(float|int|string $amount): string
    {
        return self::CURRENCY_SYMBOL . number_format((float) $amount, self::MONEY_DECIMALS);
    }

    public static function count(int $amount, string $noun): string
    {
        if ($amount === 1) {
            return $amount . ' ' . $noun;
        }

        return $amount . ' ' . $noun . 's';
    }

    public static function date(?DateTimeInterface $date, string $format = self::DATE_DISPLAY): string
    {
        if ($date === null) {
            return self::NOT_AVAILABLE;
        }

        return $date->format($format);
    }

    public static function isoDate(string $relativeTo = 'today'): string
    {
        return (new DateTimeImmutable($relativeTo))->format(self::DATE_ISO);
    }
}
