<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\DanceController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/':
    case '/home':
        (new HomeController())->index();
        break;

    default:
        http_response_code(404);
        echo 'Page not found';
}
