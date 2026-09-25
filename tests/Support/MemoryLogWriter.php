<?php

declare(strict_types=1);

namespace Tests\Support;

use Core\Logging\LogWriter;

final class MemoryLogWriter implements LogWriter
{
    /** @var string[] */
    public array $lines = [];

    public function write(string $formattedLine): void
    {
        $this->lines[] = $formattedLine;
    }
}
