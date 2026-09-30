<?php

namespace Database\Seeders\Support;

use RuntimeException;

/**
 * Loads and decodes a JSON file relative to a base directory.
 */
class JsonFileLoader
{
    public function __construct(private string $baseDir)
    {
    }

    /**
     * @return array<int|string, mixed>
     * @throws RuntimeException if the file is missing or invalid
     */
    public function load(string $filename): array
    {
        $path = rtrim($this->baseDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (!is_file($path)) {
            throw new RuntimeException("JSON file not found: $path");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Failed to read JSON file: $path");
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            throw new RuntimeException("Invalid JSON in: $path");
        }

        return $decoded;
    }
}
