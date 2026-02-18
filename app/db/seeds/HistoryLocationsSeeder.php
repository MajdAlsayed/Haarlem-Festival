<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HistoryLocationsSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $this->table('history_locations')->insert([
            [
                'name' => 'Church of St. Bavo',
                'slug' => 'st-bavo',
                'description' => 'The Church of St. Bavo rose between 1370 and 1520 as Haarlem\'s architectural and spiritual centerpiece. This magnificent Gothic cathedral, '.
                'dedicated to a 7th-century saint who renounced wealth for faith, reflected the prosperity and religious devotion of medieval Haarlem. Its soaring 80-meter tower '.
                'dominated the skyline, announcing the city\'s importance to travelers from across Holland.',
                'page_id' => null,
                'sort_order' => 1
            ],
            [
                'name' => 'Grote Markt',
                'slug' => 'grote-markt',
                'description' => 'For over 700 years, the Grote Markt has been Haarlem\'s social and economic nucleus. Medieval merchants traded cloth, fish, and grain here. '.
                'Public announcements were roclaimed from its steps. Justice was dispensed, and sometimes executed, in full public view. The square witnessed sieges, celebrations, '.
                'and the daily rhythms of urban life.',
                'page_id' => null,
                'sort_order' => 2
            ],
            [
                'name' => 'De Hallen',
                'slug' => 'de-hallen',
                'description' => 'De Hallen emerged in the early 17th century as Haarlem\'s covered markets, serving the city\'s thriving trade. The Meat Hall (Vleeshal), '.
                'designed by Lieven de Key in 1602, featured an ornate Renaissance façade decorated with ox heads and butcher symbols - a bold celebration of commercial prosperity '.
                'during the Golden Age.',
                'page_id' => null,
                'sort_order' => 3
            ],
            [
                'name' => 'Proveniershof',
                'slug' => 'proveniershof',
                'description' => 'Established in 1591, the Proveniershof exemplified Haarlem\'s unique approach to  elder care. Unlike simple almshouses, this hofje operated '.
                'as a retirement community  where prosperous citizens paid an entrance fee in exchange for lifelong accommodation, meals, and dignity. The peaceful courtyard garden, '.
                'surrounded by small apartments, created a self-contained community balancing independence with security.',
                'page_id' => null,
                'sort_order' => 4
            ],
            [
                'name' => 'Jopenkerk',
                'slug' => 'jopenkerk',
                'description' => 'Built in 1878 as a neo-Gothic Catholic church, the Jacobskerk served Haarlem\'s growing Catholic community during an era when the Netherlands '.
                'rebuilt its Catholic infrastructure after centuries of Protestant dominance. Its soaring arches and ornate decoration reflected 19th century Gothic Revival enthusiasm.',
                'page_id' => null,
                'sort_order' => 5
            ],
            [
                'name' => 'Waalse Kerk',
                'slug' => 'waalse-kerk',
                'description' => 'The Waalse Kerk tells Haarlem\'s story as a city of refuge. In the late 16th  century, thousands of Protestant Walloons - French-speaking Belgians '.
                '- fled religious  persecution in the Spanish Netherlands. Haarlem welcomed these skilled craftsmen  and merchants, granting them this church in 1591 for their French-language '.
                'services - a remarkable act of tolerance during Europe\'s religious wars. ',
                'page_id' => null,
                'sort_order' => 6
            ],
            [
                'name' => 'Molen de Adriaan',
                'slug' => 'molen-de-adriaan',
                'description' => 'Molen de Adriaan, built in 1779 on the Spaarne River, represents the Dutch mastery of wind power that shaped their landscape and economy. This smock '.
                'mill ground grain, chalk, and tobacco snuff - versatile industrial applications that fueled commerce. Windmills were essential to Dutch life: they drained polders, sawed timber, '.
                'pressed oil, and ground everything from grain to pigments.',
                'page_id' => null,
                'sort_order' => 7
            ],
            [
                'name' => 'Amsterdamse Poort',
                'slug' => 'amsterdamse-poort',
                'description' => 'The imposing Amsterdamse Poort, built in 1355, stands as Haarlem\'s only surviving  medieval city gate. Once, twelve such gates pierced the city walls, '.
                'controlling  access and defending against siege. This gate faced the road to Amsterdam, witnessing  centuries of travelers, merchants, and armies passing through its arched entrance.',
                'page_id' => null,
                'sort_order' => 8
            ],
            [
                'name' => 'Hof van Bakenes',
                'slug' => 'hof-van-bakenes',
                'description' => 'Founded in 1395, the Hof van Bakenes holds the distinction of being Haarlem\'s oldest surviving hofje. For over 600 years, this charitable institution has provided '.
                'free housing for elderly women, demonstrating remarkable continuity through centuries of social change. The hofje was established by a wealthy merchant\'s bequest - a common form of '.
                'Golden Age philanthropy.',
                'page_id' => null,
                'sort_order' => 9
            ]

        ])->saveData();
    }
}
