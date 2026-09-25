<?php

declare(strict_types=1);

namespace Tests\Support;

use Core\Request;
use Core\Response;

/**
 * Minimal controller double used to exercise the router in isolation.
 */
final class EchoController
{
    public function hello(Request $r): Response
    {
        return Response::html('hi');
    }

    public function broken(Request $r): string
    {
        return 'not a response';
    }
}
