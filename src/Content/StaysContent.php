<?php

declare(strict_types=1);

namespace Content;

use Contracts\StaysContentInterface;
use Core\View\Content;

final class StaysContent implements StaysContentInterface
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

    public function getEmpty(): array
    {
        return $this->content->get('empty');
    }

    public function getCard(): array
    {
        return $this->content->get('card');
    }
}
