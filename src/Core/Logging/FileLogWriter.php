<?php

declare(strict_types=1);

namespace Core\Logging;

use RuntimeException;

final class FileLogWriter implements LogWriter
{
    private const DIRECTORY_PERMISSIONS = 0755;

    public function __construct(private readonly string $path)
    {
        $directory = dirname($path);

        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, self::DIRECTORY_PERMISSIONS, true) && !is_dir($directory)) {
            throw new RuntimeException("Cannot create log directory: {$directory}");
        }
    }

    public function write(string $formattedLine): void
    {
        if (file_put_contents($this->path, $formattedLine, FILE_APPEND | LOCK_EX) !== false) {
            return;
        }
        error_log(rtrim($formattedLine));
    }
}
