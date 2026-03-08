<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\CartController;
use App\Controllers\StoriesController;

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$uri = rtrim($uri, '/');
if ($uri === '') $uri = '/';

switch ($uri) {
    case '/':
    case '/home':
        if ($method === 'GET') (new HomeController())->index();
        else http_response_code(405);
        break;

    case '/stories':
        if ($method === 'GET') (new StoriesController())->index();
        else http_response_code(405);
        break;

    case '/stories/venue':
        if ($method === 'GET') (new StoriesController())->venue();
        else http_response_code(405);
        break;

    case '/stories/detail':
        if ($method === 'GET') (new StoriesController())->detail();
        else http_response_code(405);
        break;

    case '/cart':
        if ($method === 'GET') (new CartController())->get();
        else http_response_code(405);
        break;

    case '/cart/add':
        if ($method === 'POST') (new CartController())->add();
        else http_response_code(405);
        break;

    case '/cart/remove':
        if ($method === 'POST') (new CartController())->remove();
        else http_response_code(405);
        break;

    case '/cart/update':
        if ($method === 'POST') (new CartController())->update();
        else http_response_code(405);
        break;

    default:
        http_response_code(404);
        echo 'Page not found';
        break;
}