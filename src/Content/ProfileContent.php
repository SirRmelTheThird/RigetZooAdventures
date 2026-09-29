<?php

declare(strict_types=1);

namespace Content;

use Contracts\ProfileContentInterface;
use Core\View\Content;

class ProfileContent implements ProfileContentInterface
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

    public function getOrders(): array
    {
        return $this->content->get('orders');
    }

    public function getAccount(): array
    {
        return $this->content->get('account');
    }

    public function getEmpty(): array
    {
        return $this->content->get('empty');
    }
}