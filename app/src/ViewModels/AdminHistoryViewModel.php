<?php

declare(strict_types=1);

namespace App\ViewModels;

final class AdminHistoryViewModel
{
    public function __construct(
        public string  $csrf,
        public array   $blocks,
        public ?string $error = null,
        public ?string $success = null,
        public ?string $slug = null
    )
    {
        $this->formSections = $this->buildFormSections();
    }

    public array $formSections = [];

    private function buildFormSections(): array
    {
        $sections = [];

        foreach ($this->blocks as $block) {
            $blockId = $block['block_id'];
            $blockType = $block['block_type'];
            $content = $block['content'] ?? [];

            // Make section for each block
            $sections[] = [
                'block_id' => $blockId,
                'block_type' => $blockType,
                'title' => $this->getSectionTitle($blockType),
                'fields' => $this->buildFields($blockType, $blockId, $content),
            ];
        }
        return $sections;
    }

    private function getSectionTitle(string $blockType): string
    {
        return match ($blockType) {
            'hero' => 'Hero Section',
            'about_banner' => 'About Banner',
            'section_header' => 'Section Header',
            'text_block' => 'Experience Section',
            'info_cards' => 'Important Information',
            'tour_details' => 'Tour Details',
            'ticket_options' => 'Ticket Options',
            'stats_bar' => 'Statistics',
            'content_section' => 'Content Section',
            'experience' => 'Experience Section',
            default => ucfirst($blockType),
        };
    }

    private function buildFields(string $blockType, int $blockId, array $content): array
    {
        return match ($blockType) {
            'hero' => [
                ['name' => "blocks[{$blockId}][title]", 'label' => 'Title', 'type' => 'text', 'value' => $content['title'] ?? ''],
                ['name' => "blocks[{$blockId}][subtitle]", 'label' => 'Subtitle', 'type' => 'textarea', 'value' => $content['subtitle'] ?? ''],
                ['name' => "blocks[{$blockId}][description]", 'label' => 'Description', 'type' => 'wysiwyg', 'value' => $content['description'] ?? ''],
                ['name' => "blocks[{$blockId}][button_text]", 'label' => 'Button text', 'type' => 'text', 'value' => $content['button_text'] ?? ''],
                ['name' => "blocks[{$blockId}][button_url]", 'label' => 'Button URL', 'type' => 'text', 'value' => $content['button_url'] ?? ''],
                ['name' => "blocks[{$blockId}][image_id]", 'label' => '', 'type' => 'image', 'value' => $content['image_id'] ?? ''],
            ],
            'about_banner' => [
                ['name' => "blocks[{$blockId}][title]", 'label' => 'Title', 'type' => 'text', 'value' => $content['title'] ?? ''],
                ['name' => "blocks[{$blockId}][text]", 'label' => 'Text', 'type' => 'wysiwyg', 'value' => $content['text'] ?? ''],
            ],
            'section_header' => [
                ['name' => "blocks[{$blockId}][title]", 'label' => 'Title', 'type' => 'text', 'value' => $content['title'] ?? ''],
                ['name' => "blocks[{$blockId}][description]", 'label' => 'Description', 'type' => 'wysiwyg', 'value' => $content['description'] ?? ''],
            ],
            'location_cards' => [],
            'text_block' => [
                ['name' => "blocks[{$blockId}][title]", 'label' => 'Title', 'type' => 'text', 'value' => $content['title'] ?? ''],
                ['name' => "blocks[{$blockId}][description]", 'label' => 'Description', 'type' => 'wysiwyg', 'value' => $content['description'] ?? ''],
                ['name' => "blocks[{$blockId}][button_text]", 'label' => 'Button text', 'type' => 'text', 'value' => $content['button_text'] ?? ''],
                ['name' => "blocks[{$blockId}][button_url]", 'label' => 'Button URL', 'type' => 'text', 'value' => $content['button_url'] ?? ''],
            ],
            'stats_bar' => [
                ['name' => "blocks[{$blockId}][stats]", 'label' => 'Stats (JSON)', 'type' => 'textarea', 'value' => json_encode($content['stats'] ?? [])],
            ],
            'experience' => [
                ['name' => "blocks[{$blockId}][title]", 'label' => 'Title', 'type' => 'text', 'value' => $content['title'] ?? ''],
            ],
            'content_section' => [
                ['name' => "blocks[{$blockId}][title]", 'label' => 'Section title', 'type' => 'text', 'value' => $content['title'] ?? ''],
            ],
            default => [],
        };
    }
}