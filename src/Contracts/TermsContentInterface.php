<?php

declare(strict_types=1);

namespace Contracts;

interface TermsContentInterface
{
    public function getData(): array;
    public function getTitle(): string;
    public function getParagraphs(): array;
}