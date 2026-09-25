<?php

declare(strict_types=1);

namespace Core\Logging;

use Throwable;

final class Logger
{
    private const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';
    private const JSON_FLAGS = JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_SLASHES;

    public function __construct(
        private readonly LogWriter $writer,
        private readonly LogLevel $minimumLevel = LogLevel::Debug,
    ) {
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::Info, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::Warning, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::Error, $message, $context);
    }

    public function exception(Throwable $e, array $context = []): void
    {
        $context['exception'] = $e::class;
        $context['file'] = $e->getFile();
        $context['line'] = $e->getLine();
        $context['trace'] = $e->getTraceAsString();

        $this->error($e->getMessage(), $context);
    }

    private function log(LogLevel $level, string $message, array $context): void
    {
        if ($level->priority() < $this->minimumLevel->priority()) {
            return;
        }

        $line = sprintf('[%s] %s: %s', date(self::TIMESTAMP_FORMAT), $level->value, $message);

        if ($context !== []) {
            $line .= ' ' . json_encode($context, self::JSON_FLAGS);
        }

        $this->writer->write($line . "\n");
    }
}
