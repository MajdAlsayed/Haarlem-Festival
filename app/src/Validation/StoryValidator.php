<?php

namespace App\Validation;

use App\Exceptions\ValidationException;

class StoryValidator
{
    // Only allow these story types
    private const ALLOWED_STORY_TYPES = ['historical', 'fictional', 'cultural', 'interactive', 'podcast', 'story', 'kids'];
    private const ALLOWED_TEMPLATES = ['generic', 'omdenken', 'buurderij'];
    private const ALLOWED_AUDIENCES = ['all-ages', 'kids', 'teens', 'adults', 'families'];

    public function validateStory(array $data): array
    {
        $errors = [];

        if (trim((string)($data['name'] ?? '')) === '') {
            $errors['name'] = 'Name is required.';
        }

        // Validate slug using Validator class
        $slug = trim((string)($data['slug'] ?? ''));
        if ($slug === '') {
            $errors['slug'] = 'Slug is required.';
        } else {
            try {
                Validator::validateSlug($slug);
            } catch (ValidationException $e) {
                $errors['slug'] = $e->getDetails()['slug'] ?? 'Invalid slug.';
            }
        }

        if (trim((string)($data['description'] ?? '')) === '') {
            $errors['description'] = 'Description is required.';
        }

        if (trim((string)($data['image_path'] ?? '')) === '') {
            $errors['image_path'] = 'Image path is required.';
        }

        // Validate story type
        if (trim((string)($data['story_type'] ?? '')) === '') {
            $errors['story_type'] = 'Story type is required.';
        } else {
            $storyType = strtolower(trim($data['story_type']));
            if (!in_array($storyType, self::ALLOWED_STORY_TYPES, true)) {
                $errors['story_type'] = 'Invalid story type. Allowed: ' . implode(', ', self::ALLOWED_STORY_TYPES);
            }
        }

        if (trim((string)($data['age'] ?? '')) === '') {
            $errors['age'] = 'Age is required.';
        }

        if (trim((string)($data['language'] ?? '')) === '') {
            $errors['language'] = 'Language is required.';
        }

        // Validate template
        $template = strtolower(trim((string)($data['template'] ?? 'generic')));
        if (!in_array($template, self::ALLOWED_TEMPLATES, true)) {
            $errors['template'] = 'Invalid template. Allowed: ' . implode(', ', self::ALLOWED_TEMPLATES);
        }

        // Validate audience
        if (trim((string)($data['audience'] ?? '')) !== '') {
            $audience = strtolower(trim($data['audience']));
            if (!in_array($audience, self::ALLOWED_AUDIENCES, true)) {
                $errors['audience'] = 'Invalid audience. Allowed: ' . implode(', ', self::ALLOWED_AUDIENCES);
            }
        }

        if ((int)($data['event_id'] ?? 0) <= 0) {
            $errors['event_id'] = 'Event ID is required.';
        }

        return $errors;
    }
}