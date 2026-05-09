<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Puts /dance/event/{id} display defaults into dance_settings (overrides still merge with dance.php).
 */
final class SeedDanceEventDetailSettings extends AbstractMigration
{
    public function up(): void
    {
        $venueCoords = [
            'Caprera Openluchttheater' => [52.4112, 4.6062],
            'Jopenkerk' => [52.3813, 4.6368],
            'Lichtfabriek' => [52.3890, 4.6330],
            'Patronaat' => [52.3820, 4.6380],
            'XO the Club' => [52.3815, 4.6370],
            'Slachthuis' => [52.3825, 4.6350],
        ];
        $dayLabels = [
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];
        $gallery = ['DetailsPage/2.png', 'DetailsPage/3.png', 'DetailsPage/4.png'];
        $map = [52.3813, 4.6368];

        $rows = [
            ['breadcrumb_home_label', 'HOME'],
            ['breadcrumb_dance_label', 'DANCE'],
            ['event_detail_photos_context', 'dance_event_detail'],
            ['event_detail_hero_fallback', 'DetailsPage/hero.png'],
            ['event_detail_gallery_fallbacks', json_encode($gallery)],
            ['event_detail_list_path', '/dance'],
            ['default_event_day', 'friday'],
            ['event_detail_venue_country', 'Netherlands'],
            ['default_map_coordinates', json_encode($map)],
            ['venue_coordinates', json_encode($venueCoords)],
            ['day_labels', json_encode($dayLabels)],
        ];

        $pdo = $this->getAdapter()->getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO dance_settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        foreach ($rows as [$key, $val]) {
            $stmt->execute(['k' => $key, 'v' => $val]);
        }
    }

    public function down(): void
    {
        $keys = [
            'breadcrumb_home_label',
            'breadcrumb_dance_label',
            'event_detail_photos_context',
            'event_detail_hero_fallback',
            'event_detail_gallery_fallbacks',
            'event_detail_list_path',
            'default_event_day',
            'event_detail_venue_country',
            'default_map_coordinates',
            'venue_coordinates',
            'day_labels',
        ];
        $pdo = $this->getAdapter()->getConnection();
        $in = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $pdo->prepare("DELETE FROM dance_settings WHERE setting_key IN ($in)");
        $stmt->execute($keys);
    }
}
