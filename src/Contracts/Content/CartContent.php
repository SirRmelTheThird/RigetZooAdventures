<?php

declare(strict_types=1);

namespace App\Models;

class CartContent implements CartContentInterface
{
    public function __construct(private readonly array $data) {}

    public function getHeader(): array
    {
        return $this->data['header'] ?? [];
    }

    public function getSummary(): array
    {
        return $this->data['summary'] ?? [];
    }

    public function getEmpty(): array
    {
        return $this->data['empty'] ?? [];
    }

    public function getItemsTitle(): string
    {
        return $this->data['items_title'] ?? '';
    }

    public function getRemoveLabel(): string
    {
        return $this->data['remove_label'] ?? '';
    }

    public function getContinue(): array
    {
        return $this->data['continue'] ?? [];
    }

    public function getClearConfirm(): string
    {
        return $this->data['clear_confirm'] ?? '';
    }
}