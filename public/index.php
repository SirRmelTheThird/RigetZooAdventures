<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

use Bootstrap\Container;
use Core\Request;
use Core\Session;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/Bootstrap.php';

Session::start();

$root = dirname(__DIR__);
$container = new Container($root);
$router = $container->router();

require $root . '/routes.php';

$request = Request::fromGlobals();

$container->errorHandler()
    ->handle(static fn () => $router->dispatch($request), $request)
    ->send();
