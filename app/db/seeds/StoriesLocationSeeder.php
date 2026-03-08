<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class StoriesLocationSeeder extends AbstractSeed
{
    public function run(): void
    {
        $cols = $this->fetchAll("SHOW COLUMNS FROM venues");
        $names = array_map(fn($c) => strtolower($c['Field']), $cols);

        $latCol = null;
        $lngCol = null;

        if (in_array('lat', $names, true) && in_array('lng', $names, true)) {
            $latCol = 'lat';
            $lngCol = 'lng';
        } elseif (in_array('latitude', $names, true) && in_array('longitude', $names, true)) {
            $latCol = 'latitude';
            $lngCol = 'longitude';
        }

        if (!$latCol || !$lngCol) {
            echo "[SKIP] venues table has no lat/lng columns\n";
            return;
        }

        $locations = [
            'De Schuur' => ['lat' => 52.3818,  'lng' => 4.63931],
            'Kweekcafé' => ['lat' => 52.39613, 'lng' => 4.63569],
        ];

        $pdo = $this->getAdapter()->getConnection();

        foreach ($locations as $name => $p) {
            $this->execute("
                UPDATE venues
                SET {$latCol} = " . $pdo->quote((string)$p['lat']) . ",
                    {$lngCol} = " . $pdo->quote((string)$p['lng']) . "
                WHERE name = " . $pdo->quote($name) . "
            ");
        }

        echo "[DONE] Updated venue coordinates\n";
    }
}