<?php

declare(strict_types=1);

namespace Content;

use Contracts\HomeContentInterface;
use Core\View\Content;

class HomeContent implements HomeContentInterface
{
    private readonly Content $content;

    public function __construct(array $data)
    {
        $this->content = new Content($data);
    }

    public function getHero(): array
    {
        return $this->content->get('hero');
    }

    public function getFacts(): array
    {
        return $this->content->get('facts');
    }

    public function getVisit(): array
    {
        return $this->content->get('visit');
    }
}
