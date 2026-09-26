<?php

declare(strict_types=1);

namespace Core\View;

use Enums\OrderStatus;

final class OrderPresenter
{
    private const LABEL_TICKET = 'Ticket';
    private const LABEL_ACCOMMODATION = 'Accommodation';
    private const LABEL_OTHER_ITEM = 'Booking item';
    private const LABEL_FLEXIBLE_DATE = 'Flexible';

    private const DATE_SHORT = 'M j';
    private const DATE_LONG = 'M j, Y';

    public static function statusClass(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Pending   => 'rz-status--pending',
            OrderStatus::Paid      => 'rz-status--paid',
            OrderStatus::Cancelled => 'rz-status--cancelled',
            OrderStatus::Failed    => 'rz-status--failed',
        };
    }

    public static function itemSummary(object $item): string
    {
        if ($item->isTicket()) {
            return self::ticketSummary($item);
        }
        if ($item->isAccommodation()) {
            return self::accommodationSummary($item);
        }

        return self::LABEL_OTHER_ITEM;
    }

    private static function ticketSummary(object $item): string
    {
        $label = self::LABEL_TICKET;
        if ($item->ticket !== null) {
            $label = $item->ticket->type . ' ' . $item->ticket->category;
        }

        $date = self::LABEL_FLEXIBLE_DATE;
        if ($item->start_date !== null) {
            $date = $item->start_date->format(self::DATE_LONG);
        }

        return sprintf('%dx %s (%s)', $item->quantity, $label, $date);
    }

    private static function accommodationSummary(object $item): string
    {
        $name = self::LABEL_ACCOMMODATION;
        if ($item->accommodation !== null) {
            $name = $item->accommodation->name;
        }

        $from = '';
        if ($item->start_date !== null) {
            $from = $item->start_date->format(self::DATE_SHORT);
        }

        $to = '';
        if ($item->end_date !== null) {
            $to = $item->end_date->format(self::DATE_LONG);
        }

        return sprintf('%s (%s to %s)', $name, $from, $to);
    }
}
