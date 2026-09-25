<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$root = dirname(__DIR__) . '/src';
$bad = 0;
$count = 0;

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $relative = substr($file->getPathname(), strlen($root) + 1, -4);

    if (str_starts_with($relative, 'Views/')) {
        continue;
    }

    $class = str_replace('/', '\\', $relative);
    $count++;

    if (!class_exists($class) && !interface_exists($class) && !enum_exists($class)) {
        echo "MISMATCH: {$relative}.php does not declare {$class}\n";
        $bad++;
    }
}

echo "checked {$count} files, {$bad} mismatches\n";
exit($bad === 0 ? 0 : 1);
