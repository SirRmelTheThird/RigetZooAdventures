<?php

declare(strict_types=1);

namespace Core\View;

final class Image
{
    private const IMAGE_PATH = '/assets/images/';
    private const WEBP_PATH = '/assets/images/WEBP/';

    public static function webp(string $src): string
    {
        $path = parse_url($src, PHP_URL_PATH);

        if (!is_string($path) || !str_starts_with($path, self::IMAGE_PATH)) {
            return $src;
        }

        $filename = basename($path);

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'], true)) {
            return $src;
        }

        $webpFilename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';

        $webpPath = self::WEBP_PATH . $webpFilename;

        $file = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $webpPath;

        return is_file($file) ? $webpPath : $src;
    }
}
