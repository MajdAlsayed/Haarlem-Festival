<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\FoodSettingsRepository;
use App\Repositories\RestaurantRepository;
use App\Repositories\SettingsRepository;
use App\Services\AdminFoodService;

/**
 * CMS admin: Food section.
 *
 * Routes handled (all require admin role):
 *   GET  /admin/food                        → index()
 *   GET  /admin/food/settings               → settings()
 *   POST /admin/food/settings               → saveSettings()
 *   GET  /admin/food/restaurants            → restaurants()
 *   GET  /admin/food/restaurants/new        → newRestaurant()
 *   GET  /admin/food/restaurants/edit?id=N  → editRestaurant()
 *   POST /admin/food/restaurants/save       → saveRestaurant()
 *   POST /admin/food/restaurants/delete     → deleteRestaurant()
 */
final class AdminFoodController
{
    private const ADMIN_ROLE_ID = 1;

    private AdminFoodService   $service;
    private SettingsRepository $appSettings;

    public function __construct()
    {
        $this->service     = new AdminFoodService(
            new RestaurantRepository(),
            new FoodSettingsRepository()
        );
        $this->appSettings = new SettingsRepository();
    }

    // -------------------------------------------------------------------------
    // Hub
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $this->requireAdmin();
        $app = $this->appSettings->getAll();
        require __DIR__ . '/../Views/Admin/Food/food-index.php';
    }

    // -------------------------------------------------------------------------
    // Settings
    // -------------------------------------------------------------------------

    public function settings(): void
    {
        $this->requireAdmin();
        $app      = $this->appSettings->getAll();
        $settings = $this->service->getAllFoodSettings();
        $success  = Session::getFlash('admin_food_success');
        $error    = Session::getFlash('admin_food_error');
        $csrf     = Csrf::token('admin_food_settings');
        require __DIR__ . '/../Views/Admin/Food/food-settings.php';
    }

    public function saveSettings(): void
    {
        $this->requireAdmin();

        if (!Csrf::validate('admin_food_settings', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_food_error', 'Invalid security token. Please try again.');
            header('Location: /admin/food/settings');
            exit;
        }

        $errors = $this->service->saveSettings($_POST);

        if ($errors !== []) {
            Session::setFlash('admin_food_error', implode(' ', $errors));
        } else {
            Session::setFlash('admin_food_success', 'Food settings saved.');
        }

        header('Location: /admin/food/settings');
        exit;
    }

    // -------------------------------------------------------------------------
    // Restaurant list
    // -------------------------------------------------------------------------

    public function restaurants(): void
    {
        $this->requireAdmin();
        $app         = $this->appSettings->getAll();
        $restaurants = $this->service->getAllRestaurants();
        $success     = Session::getFlash('admin_food_success');
        $error       = Session::getFlash('admin_food_error');
        require __DIR__ . '/../Views/Admin/Food/food-restaurants-list.php';
    }

    // -------------------------------------------------------------------------
    // New restaurant
    // -------------------------------------------------------------------------

    public function newRestaurant(): void
    {
        $this->requireAdmin();
        $app  = $this->appSettings->getAll();
        $row  = null;
        $csrf = Csrf::token('admin_food_restaurant');
        $error = Session::getFlash('admin_food_error');
        require __DIR__ . '/../Views/Admin/Food/food-restaurant-edit.php';
    }

    // -------------------------------------------------------------------------
    // Edit restaurant
    // -------------------------------------------------------------------------

    public function editRestaurant(): void
    {
        $this->requireAdmin();

        $id  = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $row = $id > 0 ? $this->service->getRestaurantById($id) : null;

        if ($row === null) {
            Session::setFlash('admin_food_error', 'Restaurant not found.');
            header('Location: /admin/food/restaurants');
            exit;
        }

        $app   = $this->appSettings->getAll();
        $csrf  = Csrf::token('admin_food_restaurant');
        $error = Session::getFlash('admin_food_error');
        require __DIR__ . '/../Views/Admin/Food/food-restaurant-edit.php';
    }

    // -------------------------------------------------------------------------
    // Save (create or update)
    // -------------------------------------------------------------------------

    public function saveRestaurant(): void
    {
        $this->requireAdmin();

        if (!Csrf::validate('admin_food_restaurant', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_food_error', 'Invalid security token. Please try again.');
            header('Location: /admin/food/restaurants');
            exit;
        }

        $id     = (int) ($_POST['restaurant_id'] ?? 0);
        $errors = $this->service->validateRestaurantPost($_POST);

        if ($errors !== []) {
            // Re-render the form with errors without losing the posted data
            $app  = $this->appSettings->getAll();
            $csrf = Csrf::token('admin_food_restaurant');
            $error = implode(' ', $errors);
            // Build a temporary Restaurant-like object from POST so the form
            // shows the user's input back
            $row = $this->service->buildRestaurantFromPost($_POST);
            require __DIR__ . '/../Views/Admin/Food/food-restaurant-edit.php';
            return;
        }

        try {
            if ($id > 0) {
                $this->service->updateRestaurant($id, $_POST);
                Session::setFlash('admin_food_success', 'Restaurant updated.');
            } else {
                $this->service->createRestaurant($_POST);
                Session::setFlash('admin_food_success', 'Restaurant created.');
            }
        } catch (\Throwable $e) {
            Session::setFlash('admin_food_error', 'Could not save: ' . $e->getMessage());
        }

        header('Location: /admin/food/restaurants');
        exit;
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function deleteRestaurant(): void
    {
        $this->requireAdmin();

        $form = (string) ($_POST['_csrf_form'] ?? '');
        if ($form === '' || !Csrf::validate($form, $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_food_error', 'Invalid request.');
            header('Location: /admin/food/restaurants');
            exit;
        }

        $id = (int) ($_POST['restaurant_id'] ?? 0);

        if ($id <= 0) {
            header('Location: /admin/food/restaurants');
            exit;
        }

        try {
            $this->service->deleteRestaurant($id);
            Session::setFlash('admin_food_success', 'Restaurant deleted.');
        } catch (\Throwable $e) {
            Session::setFlash('admin_food_error', 'Could not delete: ' . $e->getMessage());
        }

        header('Location: /admin/food/restaurants');
        exit;
    }

    // -------------------------------------------------------------------------
    // Auth helper
    // -------------------------------------------------------------------------

    private function requireAdmin(): void
    {
        $auth = $_SESSION['auth'] ?? null;
        if (!is_array($auth) || empty($auth['user_id'])) {
            Session::setFlash('login_error', 'Please log in to access the admin area.');
            header('Location: /login');
            exit;
        }
        if ((int) ($auth['role_id'] ?? 0) !== self::ADMIN_ROLE_ID) {
            Session::setFlash('login_error', 'You do not have permission to access the admin area.');
            header('Location: /');
            exit;
        }
    }
}