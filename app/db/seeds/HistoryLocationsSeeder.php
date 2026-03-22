<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HistoryLocationsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');
        $this->execute('TRUNCATE TABLE history_locations');
        // Re-enable foreign key checks
        $this->execute('SET FOREIGN_KEY_CHECKS = 1');

        $this->table('history_locations')->insert([
            [
                'name' => 'Church of St. Bavo',
                'slug' => 'st-bavo',
                'description_1' => 'The Church of St. Bavo rose between 1370 and 1520 as Haarlem\'s architectural and spiritual centerpiece. This magnificent Gothic cathedral, '.
                    'dedicated to a 7th-century saint who renounced wealth for faith, reflected the prosperity and religious devotion of medieval Haarlem. Its soaring 80-meter tower '.
                    'dominated the skyline, announcing the city\'s importance to travelers from across Holland.',
                'description_2' =>'The Reformation transformed everything in 1578. Catholic imagery was destroyed, walls whitewashed, and the building became a Protestant church. '.
                    'Yet its grandeur survived. In 1738, the legendary Christian Müller organ was installed - over 5,000 pipes creating one of Europe\'s finest instruments. '.
                    'The 10-year-old Mozart played it in 1766, cementing the church\'s place in musical history.',
                'short_description' => 'The magnificent Grote Kerk, dedicated to St. Bavo, dominates Haarlem\'s skyline with its soaring Gothic tower. Built between 1370 and 1520, '.
                    'this architectural masterpiece houses the legendary Müller organ, played by both Mozart and Handel.',
                'page_id' => null,
                'sort_order' => 1,
                'lat' => 52.3810,
                'lng' => 4.6372
            ],
            [
                'name' => 'Grote Markt',
                'slug' => 'grote-markt',
                'description_1' => 'For over 700 years, the Grote Markt has been Haarlem\'s social and economic nucleus. Medieval merchants traded cloth, fish, and grain here. '.
                    'Public announcements were roclaimed from its steps. Justice was dispensed, and sometimes executed, in full public view. The square witnessed sieges, celebrations, '.
                    'and the daily rhythms of urban life.',
                'description_2' =>'Surrounding the square are architectural gems spanning centuries: the Gothic Grote Kerk, the medieval Town Hall (expanded during the Golden Age), '.
                    'and elegant merchant houses. The market\'s strategic location at crossing trade routes helped Haarlem prosper. Saturday markets continue an 800-year tradition, '.
                    'connecting modern Haarlem to its medieval past.',
                'short_description' => 'For over 700 years, the Grote Markt has been the beating heart of Haarlem. This spacious market square witnessed medieval trade fairs, public '.
                    'executions, celebrations, and protests that shaped the city\'s destiny.',
                'page_id' => null,
                'sort_order' => 2,
                'lat' => 52.3814,
                'lng' => 4.6359
            ],
            [
                'name' => 'De Hallen',
                'slug' => 'de-hallen',
                'description_1' => 'De Hallen emerged in the early 17th century as Haarlem\'s covered markets, serving the city\'s thriving trade. The Meat Hall (Vleeshal), '.
                    'designed by Lieven de Key in 1602, featured an ornate Renaissance façade decorated with ox heads and butcher symbols - a bold celebration of commercial prosperity '.
                    'during the Golden Age.',
                'description_2' =>'For over 300 years, these halls housed the meat and cloth trades that fueled Haarlem\'s economy. Merchants conducted business protected from '.
                    'the elements while city authorities regulated quality and prevented fraud. The buildings expressed the wealth and civic ambition that defined Golden Age Haarlem.',
                'short_description' => 'De Hallen began as Haarlem\'s covered markets in the early 17th century. The magnificent Meat Hall, designed by Lieven de Key in 1602, '.
                    'featured an ornate Renaissance façade decorated with ox heads - a bold statement of Golden Age prosperity.',
                'page_id' => null,
                'sort_order' => 3,
                'lat' => 52.3811,
                'lng' => 4.6360
            ],
            [
                'name' => 'Proveniershof',
                'slug' => 'proveniershof',
                'description_1' => 'Established in 1591, the Proveniershof exemplified Haarlem\'s unique approach to  elder care. Unlike simple almshouses, this hofje operated '.
                    'as a retirement community  where prosperous citizens paid an entrance fee in exchange for lifelong accommodation, meals, and dignity. The peaceful courtyard garden, '.
                    'surrounded by small apartments, created a self-contained community balancing independence with security.',
                'description_2' =>'This model reflected the social conscience of Golden Age Haarlem, where wealth came  with responsibility for community welfare. Wealthy merchants and '.
                    'organizations funded  these hofjes not from religious obligation alone, but from civic pride - ensuring  Haarlem\'s elderly lived with dignity. The Proveniershof still '.
                    'serves its original  purpose over 400 years later.',
                'short_description' =>'Established in 1591, the Proveniershof was a unique retirement community where wealthy citizens paid an entrance fee for lifelong care. '.
                    'This tranquil hofje remains one of Haarlem\'s most peaceful hidden courtyards.',
                'page_id' => null,
                'sort_order' => 4,
                'lat' => 52.3773,
                'lng' =>  4.6307
            ],
            [
                'name' => 'Jopenkerk',
                'slug' => 'jopenkerk',
                'description_1' => 'Built in 1878 as a neo-Gothic Catholic church, the Jacobskerk served Haarlem\'s growing Catholic community during an era when the Netherlands '.
                    'rebuilt its Catholic infrastructure after centuries of Protestant dominance. Its soaring arches and ornate decoration reflected 19th century Gothic Revival enthusiasm.',
                'description_2' =>'By the late 20th century, declining attendance left many Dutch churches empty. Rather than demolish this architectural treasure, creative adaptation saved it. '.
                    'In 2010, the Jopen brewing company transformed the church into a brewery and  grand café. The name "Jopen" references medieval beer barrel sizes, connecting  modern brewing '.
                    'to Haarlem\'s historic brewing tradition during the Golden Age.',
                'short_description' =>'Built in 1878 as a neo-Gothic Catholic church, the Jopenkerk found a second life as a craft brewery. Its soaring arches and stained glass now shelter '.
                    'gleaming copper brewing vats — sacred architecture reimagined.',
                'page_id' => null,
                'sort_order' => 5,
                'lat' => 52.3812,
                'lng' => 4.6297
            ],
            [
                'name' => 'Waalse Kerk',
                'slug' => 'waalse-kerk',
                'description_1' => 'The Waalse Kerk tells Haarlem\'s story as a city of refuge. In the late 16th  century, thousands of Protestant Walloons - French-speaking Belgians '.
                    '- fled religious  persecution in the Spanish Netherlands. Haarlem welcomed these skilled craftsmen  and merchants, granting them this church in 1591 for their French-language '.
                    'services - a remarkable act of tolerance during Europe\'s religious wars. ',
                'description_2' =>'The Walloon community brought valuable expertise in textile production, particularly linen weaving and bleaching, helping establish Haarlem as a center of the Dutch '.
                    'linen industry. Their church became a symbol of successful integration, proving that religious tolerance could be both morally right and economically beneficial - a characteristic Dutch pragmatism.',
                'short_description' =>'Granted to Walloon refugees in 1591, the Waalse Kerk reflects Haarlem\'s tradition of tolerance. These Protestant craftsmen, fleeing Spanish persecution, '.
                    'enriched the city\'s culture and trade during the Dutch Golden Age.',
                'page_id' => null,
                'sort_order' => 6,
                'lat' => 52.3825,
                'lng' => 4.6390
            ],
            [
                'name' => 'Molen de Adriaan',
                'slug' => 'molen-de-adriaan',
                'description_1' => 'Molen de Adriaan, built in 1779 on the Spaarne River, represents the Dutch mastery of wind power that shaped their landscape and economy. This smock '.
                    'mill ground grain, chalk, and tobacco snuff - versatile industrial applications that fueled commerce. Windmills were essential to Dutch life: they drained polders, sawed timber, '.
                    'pressed oil, and ground everything from grain to pigments.',
                'description_2' =>'Tragedy struck in 1932 when fire destroyed the original mill. For 70 years, only  the foundation remained - a gap in Haarlem\'s skyline. In 2002, after extensive '.
                    'fundraising, a faithful reconstruction was completed. Today\'s De Adriaan operates as a working museum, its sails turning once again, grinding grain using traditional methods and '.
                    'educating visitors about Dutch windmill technology.',
                'short_description' =>'Built in 1779 on the Spaarne River, Molen de Adriaan is Haarlem\'s iconic working windmill. Destroyed by fire in 1932 and restored in 2002, it represents the '.
                    'Dutch mastery of wind power that shaped a nation.',
                'page_id' => null,
                'sort_order' => 7,
                'lat' => 52.3838,
                'lng' => 4.6427
            ],
            [
                'name' => 'Amsterdamse Poort',
                'slug' => 'amsterdamse-poort',
                'description_1' => 'The imposing Amsterdamse Poort, built in 1355, stands as Haarlem\'s only surviving  medieval city gate. Once, twelve such gates pierced the city walls, '.
                    'controlling  access and defending against siege. This gate faced the road to Amsterdam, witnessing  centuries of travelers, merchants, and armies passing through its arched entrance.',
                'description_2' =>'During Haarlem\'s devastating 1572-1573 siege by Spanish forces, gates like this became crucial defensive positions. The seven-month siege ended in surrender and massacre '.
                    '- a trauma that shaped the city\'s identity. Most medieval walls and gates were demolished in the 19th century as obstacles to growth. The Amsterdamse Poort survived, converted into housing, '.
                    'preserving Haarlem\'s fortified past.',
                'short_description' =>'Built around 1355, the Amsterdamse Poort is the last surviving medieval city gate of Haarlem. This Gothic gatehouse once controlled all traffic from Amsterdam, one '.
                    'of the best-preserved medieval gates in the Netherlands.',
                'page_id' => null,
                'sort_order' => 8,
                'lat' => 52.3806,
                'lng' => 4.6464
            ],
            [
                'name' => 'Hof van Bakenes',
                'slug' => 'hof-van-bakenes',
                'description_1' => 'Founded in 1395, the Hof van Bakenes holds the distinction of being Haarlem\'s oldest surviving hofje. For over 600 years, this charitable institution has provided '.
                    'free housing for elderly women, demonstrating remarkable continuity through centuries of social change. The hofje was established by a wealthy merchant\'s bequest - a common form of '.
                    'Golden Age philanthropy.',
                'description_2' =>'By endowing a hofje, merchants ensured their charitable legacy continued after death while securing prayers for their souls. After the Reformation, religious motivation '.
                    'disappeared, but charitable tradition persisted. The current buildings, rebuilt in the 17th century after fire, surround a tranquil garden. The Gothic gateway still bears the original 1395 date, connecting today\'s residents to six centuries of history.',
                'short_description' =>'Founded around 1395, the Hof van Bakenes is one of Haarlem\'s oldest hofjes, built to house poor women. Hidden behind an unassuming entrance, this serene courtyard '.
                    'offers a glimpse into six centuries of charitable life.',
                'page_id' => null,
                'sort_order' => 9,
                'lat' => 52.3815,
                'lng' => 4.6399
            ]

        ])->saveData();
    }
}
