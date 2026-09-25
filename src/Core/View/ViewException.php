<?php

declare(strict_types=1);

namespace Core\View;

use RuntimeException;

final class ViewException extends RuntimeException
{
    public static function notBound(): self
    {
        return new self('View::bind() must be called before rendering partials or content.');
    }

    public static function partialNotFound(string $name): self
    {
        return new self(sprintf('View partial "%s" does not exist.', $name));
    }

    public static function contentNotFound(string $name): self
    {
        return new self(sprintf('View content file "%s" does not exist.', $name));
    }

    public static function invalidContent(string $name): self
    {
        return new self(sprintf('View content file "%s" must return an array.', $name));
    }

    public static function missingTicketPrice(TicketCategory $category): self
    {
        return new self(sprintf('No "%s" ticket price found for this ticket tier.', $category->value));
    }
}
