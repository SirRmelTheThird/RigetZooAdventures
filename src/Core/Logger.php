<?php

namespace Core;

class Logger
{
    private static $logPath;

    private static function init()
    {
        if (!self::$logPath) {
            self::$logPath = dirname(__DIR__, 2) . '/storage/logs/app.log';

            $logsDir = dirname(self::$logPath);
            if (!is_dir($logsDir)) {
                mkdir($logsDir, 0755, true);
            }
        }
    }

    public static function info($message, $context = [])
    {
        self::log('INFO', $message, $context);
    }

    public static function error($message, $context = [])
    {
        self::log('ERROR', $message, $context);
    }

    public static function warning($message, $context = [])
    {
        self::log('WARNING', $message, $context);
    }

    public static function debug($message, $context = [])
    {
        if (\Config\Config::isDebug()) {
            self::log('DEBUG', $message, $context);
        }
    }

    public static function critical($message, $context = [])
    {
        self::log('CRITICAL', $message, $context);
    }

    private static function log($level, $message, $context)
    {
        self::init();

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[$timestamp] $level: $message$contextStr\n";

        if ($level === 'CRITICAL' || $level === 'ERROR') {
            error_log($message);
        }

        file_put_contents(self::$logPath, $logMessage, FILE_APPEND);
    }

    public static function exception(\Exception $e, $context = [])
    {
        $context['exception'] = get_class($e);
        $context['file'] = $e->getFile();
        $context['line'] = $e->getLine();
        $context['trace'] = $e->getTraceAsString();

        self::error($e->getMessage(), $context);
    }
}
