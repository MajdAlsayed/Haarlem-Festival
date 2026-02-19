<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\DanceController;
use App\Controllers\HistoryController;
use App\Exceptions\AppException;
use App\Exceptions\NotFoundException;

set_exception_handler(function (Throwable $e): void {
    $code = 500;
    $message = 'An error occurred.';

    if ($e instanceof AppException) {
        $code = $e->getHttpCode();
        $message = $e->getMessage();
    }

    http_response_code($code);
    require __DIR__ . '/../src/Views/error.php';
});

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/':
    case '/home':
        (new HomeController())->index();
        break;

    case '/dance':
        (new DanceController())->index();
        break;

    case '/history':
        (new HistoryController())->index();
        break;

    default:
        throw new NotFoundException('Page not found');
}
