<?php

declare(strict_types=1);

namespace Content;

use Contracts\EducationalContentInterface;
use Core\View\Content;

final class EducationalContent implements EducationalContentInterface
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

    public function getFeature(): array
    {
        return $this->content->get('feature');
    }

    public function getTopics(): array
    {
        return $this->content->get('topics');
    }
}