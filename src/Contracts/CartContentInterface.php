<?php

declare(strict_types=1);

namespace App\Models;

interface CartContentInterface
{
    public function getHeader(): array;
    public function getSummary(): array;
    public function getEmpty(): array;
    public function getItemsTitle(): string;
    public function getRemoveLabel(): string;
    public function getContinue(): array;
    public function getClearConfirm(): string;
}