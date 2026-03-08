<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\DanceController;
use App\Controllers\EventDetailController;
use App\Controllers\ArtistController;
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
    // require runs in this scope so error.php sees $code and $message
    require __DIR__ . '/../src/Views/error.php';
});

// PHP_URL_PATH = path only, no query string
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Check these before switch so /dance/event/1 doesn't hit default
if (preg_match('#^/dance/event/(\d+)$#', $uri, $m)) {
    (new EventDetailController())->show((int) $m[1]);
} elseif (preg_match('#^/dance/artist/([a-z0-9-]+)$#', $uri, $m)) {
    (new ArtistController())->show($m[1]);
} else {
    switch ($uri) {
        case '/':
        case '/home':
            (new HomeController())->index();
            break;

        case '/dance':
            (new DanceController())->index();
            break;

        default:
            throw new NotFoundException('Page not found');
    }
}
