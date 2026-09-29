<?php

declare(strict_types=1);

namespace Content;

use Contracts\TermsContentInterface;
use Core\View\Content;

final class TermsContent implements TermsContentInterface
{
    private readonly Content $content;

    public function __construct(private readonly array $data)
    {
        $this->content = new Content($data);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTitle(): string
    {
        return (string) $this->content->get('title');
    }

    public function getParagraphs(): array
    {
        return $this->content->get('paragraphs');
    }
}