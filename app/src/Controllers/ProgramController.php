<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\PersonalProgramRepository;
use App\Repositories\SettingsRepository;

/**
 * Saved ticket picks (personal program) when the customer adds items to the cart while logged in.
 */
final class ProgramController
{
    public function index(): void
    {
        $uid = isset($_SESSION['auth']['user_id']) ? (int) $_SESSION['auth']['user_id'] : 0;
        if ($uid <= 0) {
            header('Location: /login?return=/my-program');
            exit;
        }

        $items = (new PersonalProgramRepository())->listForUser($uid);
        $app = (new SettingsRepository())->getAll();

        require __DIR__ . '/../Views/Account/program.php';
    }
}
