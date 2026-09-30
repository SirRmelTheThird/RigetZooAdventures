<?php

declare(strict_types=1);

namespace Content;

use Contracts\CartContentInterface;
use Core\View\Content;

class CartContent implements CartContentInterface
{
    private readonly Content $content;

    public function __construct(array $data)
    {
        $this->content = new Content($data);
    }

    public function getHeader(): array
    {
        return $this->content->get('header');
    }

    public function getSummary(): array
    {
        return $this->content->get('summary');
    }

    public function getEmpty(): array
    {
        return $this->content->get('empty');
    }

    public function getItemsTitle(): string
    {
        return (string) $this->content->get('items_title');
    }

    public function getRemoveLabel(): string
    {
        return (string) $this->content->get('remove_label');
    }

    public function getContinue(): array
    {
        return $this->content->get('continue');
    }

    public function getClearConfirm(): string
    {
        return (string) $this->content->get('clear_confirm');
    }
}
