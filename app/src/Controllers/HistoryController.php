<?php

namespace App\Controllers;

use App\Repositories\IHistoryRepository;
use App\Repositories\HistoryRepository;

class HistoryController
{
    private IHistoryRepository $historyRepository;

    public function __construct()
    {
        $this->historyRepository = new HistoryRepository();
    }

    public function index(): void
    {
        $locations = $this->historyRepository->getAllLocations();
        require __DIR__ . '/../Views/History/Index.php';
    }
}