<?php

declare(strict_types=1);

namespace Content;

use Contracts\CheckoutContentInterface;
use Core\View\Content;

class CheckoutContent implements CheckoutContentInterface
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

    public function getPayment(): array
    {
        return $this->content->get('payment');
    }

    public function getSummary(): array
    {
        return $this->content->get('summary');
    }
}