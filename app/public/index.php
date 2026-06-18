<?php

ob_start();

require_once __DIR__ . '/../vendor/autoload.php';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$uri = rtrim($uri, '/');
if ($uri === '') $uri = '/';
use App\Core\Session;
use App\Controllers\AuthController;
use App\Controllers\DanceController;
use App\Controllers\EventDetailController;
use App\Controllers\ArtistController;
use App\Controllers\FoodController;
use App\Controllers\HomeController;
use App\Controllers\HistoryController;
use App\Controllers\JazzController;
use App\Exceptions\AppException;
use App\Exceptions\NotFoundException;
use App\Controllers\StoriesController;
use App\Controllers\AdminController;
use App\Controllers\AdminJazzController;
use App\Controllers\AdminTicketsController;
use App\Controllers\AdminOrderExportController;
use App\Controllers\AdminOrdersController;
use App\Controllers\AdminHomepageController;
use App\Controllers\AdminCmsUploadController;
use App\Controllers\AdminDanceController;
use App\Controllers\AdminHistoryController;
use App\Controllers\AdminFoodController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\TicketScanController;
use App\Controllers\TicketsController;
use App\Controllers\AccountController;
use App\Controllers\ProgramController;
use App\Controllers\AdminUserController;
use App\Core\SecurityHeaders;
use App\Services\PendingOrderMaintenance;
use App\Controllers\AdminStoriesController;
Session::start();
// Pay-later maintenance: expire stale reservations and send reminders (non-fatal errors inside the service).
PendingOrderMaintenance::run();
SecurityHeaders::send();

set_exception_handler(function (Throwable $e): void {
    $code    = 500;
    $message = 'An error occurred.';

    if ($e instanceof AppException) {
        $code    = $e->getHttpCode();
        $message = $e->getMessage();
    }

    if (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($code);
    require __DIR__ . '/../src/Views/error.php';
});

if (preg_match('#^/food/restaurant/(\d+)$#', $uri, $m)) {
    (new FoodController())->restaurant((int) $m[1]);
    exit;
}
if (preg_match('#^/food/restaurant/(\d+)/booking$#', $uri, $m)) {
    if ($method !== 'GET' && $method !== 'POST') {
        http_response_code(405);
        exit;
    }
    (new FoodController())->booking((int) $m[1]);
    exit;
}
if (preg_match('#^/food/restaurant/(\d+)/booking/overview$#', $uri, $m)) {
    if ($method !== 'GET' && $method !== 'POST') { http_response_code(405); exit; }
    (new FoodController())->bookingOverview((int) $m[1]);
    exit;
}
// Dance: event and artist detail URLs (index is /dance via DanceController).
if (preg_match('#^/dance/event/(\d+)$#', $uri, $m)) {
    (new EventDetailController())->show((int) $m[1]);
    exit;
}
if (preg_match('#^/dance/artist/([a-z0-9-]+)$#', $uri, $m)) {
    (new ArtistController())->show($m[1]);
    exit;
}

if (preg_match('#^/history/location/([a-z0-9-]+)$#', $uri, $m)) {
    (new HistoryController())->show($m[1]);
    exit;
}

if (preg_match('#^/admin/cms/history/location/([a-z0-9-]+)$#', $uri, $m)) {
    $historyLocationCms = new AdminHistoryController();
    if ($method === 'GET') $historyLocationCms->showLocationForm($m[1]);
    elseif ($method === 'POST') $historyLocationCms->saveLocation($m[1]);
    else http_response_code(405);
    exit;
}

if (preg_match('#^/account/order/(\d+)/invoice$#', $uri, $m)) {
    if ($method === 'GET') {
        (new AccountController())->downloadInvoice((int) $m[1]);
    } else {
        http_response_code(405);
    }
    exit;
}

if (preg_match('#^/account/order/(\d+)/tickets$#', $uri, $m)) {
    if ($method === 'GET') {
        (new AccountController())->downloadTickets((int) $m[1]);
    } else {
        http_response_code(405);
    }
    exit;
}

if (preg_match('#^/account/order/(\d+)$#', $uri, $m)) {
    if ($method === 'GET') {
        (new AccountController())->orderDetail((int) $m[1]);
    } else {
        http_response_code(405);
    }
    exit;
}

switch ($uri) {
    case '/':
    case '/home':
        if ($method === 'GET') (new HomeController())->index();
        else http_response_code(405);
        break;

    case '/stories':
        if ($method === 'GET') (new StoriesController())->home();
        else http_response_code(405);
        break;

    case '/stories/events':
        if ($method === 'GET') (new StoriesController())->events();
        else http_response_code(405);
        break;

    case '/stories/detail':
        if ($method === 'GET') (new StoriesController())->detail();
        else http_response_code(405);
        break;
       
case '/api/stories':
    if ($method === 'GET') {
        (new StoriesController())->apiStories();
    } else {
        http_response_code(405);
    }
    break;

    case '/cart':
        if ($method === 'GET') {
            (new CartController())->get();
        } else {
            http_response_code(405);
        }
        break;

    case '/api/cart':
        if ($method === 'GET') {
            (new CartController())->apiGet();
        } else {
            http_response_code(405);
        }
        break;

    case '/cart/add':
        if ($method === 'POST') {
            (new CartController())->add();
        } else {
            http_response_code(405);
        }
        break;

    case '/api/cart/add':
        if ($method === 'POST') {
            (new CartController())->apiAdd();
        } else {
            http_response_code(405);
        }
        break;

    case '/cart/remove':
        if ($method === 'POST') {
            (new CartController())->remove();
        } else {
            http_response_code(405);
        }
        break;

    case '/api/cart/update':
        if ($method === 'POST') {
            (new CartController())->apiUpdate();
        } else {
            http_response_code(405);
        }
        break;

    case '/api/cart/remove':
        if ($method === 'POST') {
            (new CartController())->apiRemove();
        } else {
            http_response_code(405);
        }
        break;
        case '/admin/stories':
        case '/cms/stories':
        if ($method === 'GET') {
            (new AdminStoriesController())->index();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/stories/edit':
        case '/cms/stories/edit':
        if ($method === 'GET') {
            (new AdminStoriesController())->edit();
        } else {
            http_response_code(405);
        }
        break;
case '/admin/stories/create':
    case '/cms/stories/create':
    if ($method === 'GET') {
        (new AdminStoriesController())->create();
    } else {
        http_response_code(405);
    }
    break;

case '/admin/stories/store':
    case '/cms/stories/store':
    if ($method === 'POST') {
        (new AdminStoriesController())->store();
    } else {
        http_response_code(405);
    }
    break;

case '/admin/stories/edit':
    case '/cms/stories/edit':
    if ($method === 'GET') {
        (new AdminStoriesController())->edit();
    } else {
        http_response_code(405);
    }
    break;

    case '/admin/stories/update':
        case '/cms/stories/update':
        if ($method === 'POST') {
            (new AdminStoriesController())->update();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/stories/delete':
        case '/cms/stories/delete':
        if ($method === 'GET' || $method === 'POST') {
            (new AdminStoriesController())->delete();
        } else {
            http_response_code(405);
        }
        break;
case '/admin/stories/delete':
    case '/cms/stories/delete':
    if ($method === 'POST') {
        (new AdminStoriesController())->delete();
    } else {
        http_response_code(405);
    }
    break;

    case '/admin/stories/detail-page':
        case '/cms/stories/detail-page':
        if ($method === 'GET') {
            (new AdminStoriesController())->editDetailPage();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/stories/detail-page/save':
        case '/cms/stories/detail-page/save':
        if ($method === 'POST') {
            (new AdminStoriesController())->saveDetailPage();
        } else {
            http_response_code(405);
        }
        break;

    case '/checkout':
        if ($method === 'GET') {
            (new CheckoutController())->show();
        } else {
            http_response_code(405);
        }
        break;

    case '/checkout/pay':
        if ($method === 'POST') {
            (new CheckoutController())->pay();
        } else {
            http_response_code(405);
        }
        break;

    case '/checkout/pay-stripe':
        if ($method === 'POST') {
            (new CheckoutController())->payStripe();
        } else {
            http_response_code(405);
        }
        break;

    case '/checkout/pay-later':
        if ($method === 'POST') {
            (new CheckoutController())->payLater();
        } else {
            http_response_code(405);
        }
        break;

    case '/checkout/pay-pending':
        if ($method === 'POST') {
            (new CheckoutController())->payPending();
        } else {
            http_response_code(405);
        }
        break;

    case '/checkout/pay-pending-stripe':
        if ($method === 'POST') {
            (new CheckoutController())->payPendingStripe();
        } else {
            http_response_code(405);
        }
        break;

    case '/checkout/cancel':
        if ($method === 'GET') {
            (new CheckoutController())->cancel();
        } else {
            http_response_code(405);
        }
        break;

    case '/checkout/success':
        if ($method === 'GET') {
            (new CheckoutController())->success();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/scan':
        $scan = new TicketScanController();
        if ($method === 'POST') {
            $scan->scan();
        } elseif ($method === 'GET') {
            $scan->index();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/users':
        if ($method === 'GET') {
            (new AdminUserController())->index();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/users/edit':
        if ($method === 'GET') {
            (new AdminUserController())->edit();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/users/update':
        if ($method === 'POST') {
            (new AdminUserController())->update();
        } else {
            header('Location: /admin/users');
            exit;
        }
        break;

    case '/admin/users/delete':
        if ($method === 'POST') {
            (new AdminUserController())->delete();
        } else {
            header('Location: /admin/users');
            exit;
        }
        break;

    case '/dance':
        (new DanceController())->index();
        break;

    case '/food':
        (new FoodController())->index();
        break;

    case '/tickets':
        if ($method === 'GET') (new TicketsController())->index();
        else http_response_code(405);
        break;

    case '/account/orders':
        if ($method === 'GET') {
            (new AccountController())->orders();
        } else {
            http_response_code(405);
        }
        break;

    case '/my-program':
        if ($method === 'GET') {
            (new ProgramController())->index();
        } else {
            http_response_code(405);
        }
        break;

    case '/login':
        $c = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $c->login();
        else $c->showLogin();
        break;

    case '/register':
        $c = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $c->register();
        else $c->showRegister();
        break;

    case '/reset-password':
        $c = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $c->resetPassword();
        else $c->showResetPassword();
        break;

    case '/forgot-password':
        $c = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $c->forgotPassword();
        else $c->showForgotPassword();
        break;

    case '/logout':
        (new AuthController())->logout();
        break;

    case '/admin/orders/export':
        if ($method === 'GET' || $method === 'POST') {
            (new AdminOrderExportController())->handle();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/orders':
        if ($method === 'GET') {
            (new AdminOrdersController())->index();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/orders/view':
        if ($method === 'GET') {
            (new AdminOrdersController())->show();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/orders/tickets':
        if ($method === 'GET') {
            (new AdminOrdersController())->tickets();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/cms/homepage':
        $cms = new AdminHomepageController();
        if ($method === 'GET') {
            $cms->showForm();
        } elseif ($method === 'POST') {
            $cms->save();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/cms/dance':
        $danceCms = new AdminDanceController();
        if ($method === 'GET') {
            $danceCms->showForm();
        } elseif ($method === 'POST') {
            $danceCms->save();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/cms/upload':
        if ($method === 'POST') {
            (new AdminCmsUploadController())->handle();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/history':
        (new AdminHistoryController())->index();
        break;

    case '/admin/cms/history':
        $historyCms = new AdminHistoryController();
        if ($method === 'GET') $historyCms->showIndexForm();
        elseif ($method === 'POST') $historyCms->saveIndex();
        else http_response_code(405);
        break;

    case '/admin/cms/history-locations':
        $historyLocationsCms = new AdminHistoryController();
        if ($method === 'GET') $historyLocationsCms->showLocationsForm();
        elseif ($method === 'POST') $historyLocationsCms->saveLocations();
        else http_response_code(405);
        break;

    case '/admin/cms/history-tours':
        $historyToursCms = new AdminHistoryController();
        if ($method === 'GET') $historyToursCms->showToursForm();
        elseif ($method === 'POST') $historyToursCms->saveTours();
        else http_response_code(405);
        break;

    case '/admin/api/history/images':
        if ($method === 'GET') {
            (new AdminHistoryController())->getImages();
        } else {
            http_response_code(405);
        }
        break;

    case '/history':
        (new HistoryController())->index();
        break;

    case '/history/locations':
        (new HistoryController())->locations();
        break;

    case '/history/tours':
        (new HistoryController())->tours();
        break;

    case '/jazz':
    case '/jazz/':
        (new JazzController())->index();
        break;

    case '/jazz/gumbo-kings':
        (new JazzController())->gumboKings();
        break;

    case '/jazz/karsu':
        (new JazzController())->karsu();
        break;

    case '/jazz/gare-du-nord':
        (new JazzController())->gareDuNord();
        break;

    case '/admin':
        (new AdminController())->index();
        break;

    case '/admin/jazz':
        (new AdminJazzController())->index();
        break;

    case '/admin/dance':
        (new AdminDanceController())->index();
        break;

    case '/admin/dance/events':
        (new AdminDanceController())->events();
        break;

    case '/admin/dance/events/new':
        (new AdminDanceController())->newEvent();
        break;

    case '/admin/dance/events/edit':
        (new AdminDanceController())->editEvent();
        break;

    case '/admin/dance/events/save':
        if ($method === 'POST') {
            (new AdminDanceController())->saveEvent();
        } else {
            header('Location: /admin/dance/events');
            exit;
        }
        break;

    case '/admin/dance/events/delete':
        if ($method === 'POST') {
            (new AdminDanceController())->deleteEvent();
        } else {
            header('Location: /admin/dance/events');
            exit;
        }
        break;

    case '/admin/dance/artists':
        (new AdminDanceController())->artists();
        break;

    case '/admin/dance/artists/new':
        (new AdminDanceController())->artistsNew();
        break;

    case '/admin/dance/artists/edit':
        (new AdminDanceController())->artistsEdit();
        break;

    case '/admin/dance/artists/save':
        if ($method === 'POST') {
            (new AdminDanceController())->saveArtist();
        } else {
            header('Location: /admin/dance/artists');
            exit;
        }
        break;

    case '/admin/dance/artists/delete':
        if ($method === 'POST') {
            (new AdminDanceController())->deleteArtist();
        } else {
            header('Location: /admin/dance/artists');
            exit;
        }
        break;

    case '/admin/jazz/events':
        (new AdminJazzController())->events();
        break;

    case '/admin/jazz/events/new':
        (new AdminJazzController())->newEvent();
        break;

    case '/admin/jazz/events/edit':
        (new AdminJazzController())->editEvent();
        break;

    case '/admin/jazz/events/save':
        if ($method === 'POST') (new AdminJazzController())->saveEvent();
        else { header('Location: /admin/jazz/events'); exit; }
        break;

    case '/admin/jazz/events/delete':
        if ($method === 'POST') (new AdminJazzController())->deleteEvent();
        else { header('Location: /admin/jazz/events'); exit; }
        break;

    case '/admin/jazz/settings':
        (new AdminJazzController())->settings();
        break;

    case '/admin/jazz/discography':
        (new AdminJazzController())->discography();
        break;

    case '/admin/jazz/discography/edit':
        (new AdminJazzController())->editDiscTrack();
        break;

    case '/admin/jazz/discography/save':
        if ($method === 'POST') (new AdminJazzController())->saveDiscTrack();
        else { header('Location: /admin/jazz/discography'); exit; }
        break;

    case '/admin/jazz/discography/delete':
        if ($method === 'POST') (new AdminJazzController())->deleteDiscTrack();
        else { header('Location: /admin/jazz/discography'); exit; }
        break;

    case '/admin/jazz/band-members':
        (new AdminJazzController())->bandMembers();
        break;

    case '/admin/jazz/band-members/edit':
        (new AdminJazzController())->editBandMember();
        break;

    case '/admin/jazz/band-members/save':
        if ($method === 'POST') {
            (new AdminJazzController())->saveBandMember();
        } else {
            header('Location: /admin/jazz/band-members');
            exit;
        }
        break;

    case '/admin/jazz/band-members/delete':
        if ($method === 'POST') {
            (new AdminJazzController())->deleteBandMember();
        } else {
            header('Location: /admin/jazz/band-members');
            exit;
        }
        break;

    case '/admin/tickets':
        (new AdminTicketsController())->index();
        break;

    case '/admin/tickets/settings':
        (new AdminTicketsController())->settings();
        break;

    case '/admin/tickets/edit':
        (new AdminTicketsController())->edit();
        break;

    case '/admin/tickets/new':
        (new AdminTicketsController())->newTicket();
        break;

    case '/admin/tickets/save':
        if ($method === 'POST') (new AdminTicketsController())->save();
        else { header('Location: /admin/tickets'); exit; }
        break;

    case '/admin/tickets/delete':
        if ($method === 'POST') (new AdminTicketsController())->delete();
        else { header('Location: /admin/tickets'); exit; }
        break;

    case '/admin/tickets/delete-bulk':
        if ($method === 'POST') {
            (new AdminTicketsController())->deleteBulk();
        } else {
            header('Location: /admin/tickets');
            exit;
        }
        break;
    case '/admin/food':
        (new AdminFoodController())->index();
        break;
 
    case '/admin/food/settings':
        $food = new AdminFoodController();
        if ($method === 'POST') {
            $food->saveSettings();
        } else {
            $food->settings();
        }
        break;
 
    case '/admin/food/restaurants':
        if ($method === 'GET') {
            (new AdminFoodController())->restaurants();
        } else {
            http_response_code(405);
        }
        break;
 
    case '/admin/food/restaurants/new':
        if ($method === 'GET') {
            (new AdminFoodController())->newRestaurant();
        } else {
            http_response_code(405);
        }
        break;
 
    case '/admin/food/restaurants/edit':
        if ($method === 'GET') {
            (new AdminFoodController())->editRestaurant();
        } else {
            http_response_code(405);
        }
        break;
 
    case '/admin/food/restaurants/save':
        if ($method === 'POST') {
            (new AdminFoodController())->saveRestaurant();
        } else {
            header('Location: /admin/food/restaurants');
            exit;
        }
        break;
 
    case '/admin/food/restaurants/delete':
        if ($method === 'POST') {
            (new AdminFoodController())->deleteRestaurant();
        } else {
            header('Location: /admin/food/restaurants');
            exit;
        }
        break;

    default:
        http_response_code(404);
        $code = 404;
        $message = 'Page not found';
        require __DIR__ . '/../src/Views/error.php';
        break;


}
