<?php

declare(strict_types=1);

namespace Content;

use Contracts\AttractionsContentInterface;
use Core\View\Content;

final class AttractionsContent implements AttractionsContentInterface
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

    public function getFeatures(): array
    {
        return $this->content->get('features');
    }

    public function getClosing(): array
    {
        return $this->content->get('closing');
    }
}