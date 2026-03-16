<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\JazzRepository;
use App\Repositories\JazzSettingsRepository;
use App\ViewModels\JazzArtistViewModel;
use App\ViewModels\JazzViewModel;

final class JazzController
{
    private JazzRepository $jazzRepo;
    private JazzSettingsRepository $settingsRepo;

    public function __construct()
    {
        $this->jazzRepo = new JazzRepository();
        $this->settingsRepo = new JazzSettingsRepository();
    }

    public function index(): void
    {
        $events = $this->jazzRepo->getAll();
        $jazzConfig = require __DIR__ . '/../Config/jazz.php';
        $eventCardImages = $jazzConfig['event_card_images'] ?? [];
        $allEventsOrder = $jazzConfig['all_events_order'] ?? [];
        $vm = new JazzViewModel($events, 'Jazz Festival', $eventCardImages, $allEventsOrder);

        require __DIR__ . '/../Views/Jazz/jazz-home.php';
    }

    public function gumboKings(): void
    {
        $this->renderArtist('gumbo-kings', __DIR__ . '/../Views/Jazz/gumbo-king.php');
    }

    public function karsu(): void
    {
        $this->renderArtist('karsu', __DIR__ . '/../Views/Jazz/karsu.php');
    }

    public function gareDuNord(): void
    {
        $this->renderArtist('gare-du-nord', __DIR__ . '/../Views/Jazz/gare-du-nord.php');
    }

    private function renderArtist(string $slug, string $viewFile): void
    {
        $settings = $this->settingsRepo->getAll();
        $artistPages = $settings['artist_pages'] ?? (require __DIR__ . '/../Config/jazz.php')['artist_pages'];

        $page = $artistPages[$slug] ?? null;
        $title = $page['title'] ?? ucfirst(str_replace('-', ' ', $slug));
        $tagline = $page['tagline'] ?? '';
        $heroFile = $page['hero_image'] ?? 'hero-jazz.jpg';
        $heroImage = '/images/jazz/' . rawurlencode($heroFile);

        $events = $this->jazzRepo->getByTitle($title);

        // Bio is stored on events.description (first matching event row)
        $bio = ($events[0]['description'] ?? '') ?: 'Artist bio placeholder (edit in DB: events.description).';

        $viewModel = new JazzArtistViewModel($slug, $title, $tagline, $heroImage, $bio, $events);

        require $viewFile;
    }
}