<?php

declare(strict_types=1);

namespace Core\Logging;

interface LogWriter
{
    public function write(string $formattedLine): void;
}
