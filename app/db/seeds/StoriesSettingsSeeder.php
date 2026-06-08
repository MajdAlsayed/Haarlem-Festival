<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class StoriesSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->table('stories_settings')->getAdapter()->execute('DELETE FROM stories_settings');

        $data = [
            // ─── Hero Images ───
            ['setting_key' => 'hero_image_1', 'setting_value' => '/images/Stories/stories-home-image-main1.png'],
            ['setting_key' => 'hero_image_2', 'setting_value' => '/images/Stories/stories-home-image-main2.jpg'],
            ['setting_key' => 'hero_image_3', 'setting_value' => '/images/Stories/stories-home-image-main3.jpg'],
            ['setting_key' => 'hero_image_4', 'setting_value' => '/images/Stories/stories-home-image-main4.jpeg'],


            // ─── Home Page Hero Section ───
            ['setting_key' => 'home_hero_heading', 'setting_value' => 'Welcome to Stories In Haarlem'],
            ['setting_key' => 'home_hero_tagline', 'setting_value' => 'Experience Haarlem Through Stories – Past, Present & Future.'],

            // ─── Home Page Intro Section ───
            ['setting_key' => 'home_intro_heading', 'setting_value' => 'The City That Speaks Through Its People'],
            ['setting_key' => 'home_intro_text', 'setting_value' => "Haarlem's rich tradition of storytelling lives in every corner of the city — from narrow cobblestone streets to centuries-old courtyards. During Stories in Haarlem, local residents, historians, and performers bring hidden tales to life through intimate sessions that reveal the city's humor, heart, and heritage."],

            // ─── Home Page What You Can Explore Section ───
            ['setting_key' => 'home_explore_title', 'setting_value' => 'What You Can Explore'],
            ['setting_key' => 'home_explore_items', 'setting_value' => json_encode([
                [
                    'title' => 'Family Stories',
                    'description' => 'Playful and engaging stories for children and families.',
                ],
                [
                    'title' => 'Live Podcasts',
                    'description' => 'Thought-provoking conversations and storytelling with audience.',
                ],
                [
                    'title' => 'History & Local Voices',
                    'description' => 'Discover Haarlem through history, memories, and personal stories.',
                ],
            ])],

            // ─── Home Page Events Section ───
            ['setting_key' => 'home_events_title', 'setting_value' => '15 Events That Tell Haarlem\'s Story'],
            ['setting_key' => 'home_events_subtitle', 'setting_value' => 'From grand churches to hidden courtyards, each landmark showcases Haarlem\'s transformation from medieval town to cultural treasure.'],

            // ─── Home Page Featured Stories Section ───
            ['setting_key' => 'home_featured_heading', 'setting_value' => 'Featured Stories'],

            // ─── Home Page Echoes of History Section ───
            ['setting_key' => 'home_echoes_title', 'setting_value' => 'Echoes of History: Stories of'],
            ['setting_key' => 'home_echoes_subtitle', 'setting_value' => 'Explore the rich narratives woven into Haarlem\'s historic streets. This guided experience introduces you to significant sites and the stories behind their importance in the city\'s history.'],
            ['setting_key' => 'home_echoes_button', 'setting_value' => 'View our Stories'],

            // ─── Home Page About Stories Section ───
            ['setting_key' => 'home_about_title', 'setting_value' => 'About Stories'],
            ['setting_key' => 'home_about_text', 'setting_value' => 'Stories in Haarlem brings together voices, memories, and imagination from across the city. Through live performances, family-friendly tales, podcasts, and historical storytelling, visitors can lose themselves in a new and meaningful way. Each event takes place in a unique venue, creating a special interaction between the storyteller, the place, and the audience. Immersive and enchanting, the Stories Invites everyone to experience the city through storytelling.'],

            // ─── Events Page Hero Section ───
            ['setting_key' => 'events_hero_image_1', 'setting_value' => '/images/Stories/event-main1.png'],
            ['setting_key' => 'events_hero_image_2', 'setting_value' => '/images/Stories/event-main2.png'],
            ['setting_key' => 'events_hero_heading', 'setting_value' => 'The Event of Haarlem Stories'],
            ['setting_key' => 'events_hero_tagline', 'setting_value' => 'Experience Haarlem Through Stories – Past, Present & Future.'],

            // ─── Events Page Intro Section ───
            ['setting_key' => 'events_intro_heading', 'setting_value' => 'Discover Stories Across Haarlem'],
            ['setting_key' => 'events_intro_text', 'setting_value' => 'Explore storytelling events across Haarlem and discover experiences for different ages and interests. From playful family stories to inspiring talks and unique performances, each venue offers its own special atmosphere and story.'],

            // ─── Events Page Schedule Section ───
            ['setting_key' => 'events_schedule_label', 'setting_value' => 'Select the Day:'],
            ['setting_key' => 'events_map_title', 'setting_value' => 'Places To Visit For Events'],
            ['setting_key' => 'events_map_subtitle', 'setting_value' => 'Location: Haarlem, Netherlands'],
        ];

        $this->table('stories_settings')->insert($data)->saveData();
    }
}
