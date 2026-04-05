<?php

namespace App\Validation;

class StoryValidator
{
    public function validateStory(array $data): array
    {
        $errors = [];

        if (trim((string)($data['name'] ?? '')) === '') {
            $errors['name'] = 'Name is required.';
        }

        if (trim((string)($data['slug'] ?? '')) === '') {
            $errors['slug'] = 'Slug is required.';
        }

        if (trim((string)($data['description'] ?? '')) === '') {
            $errors['description'] = 'Description is required.';
        }

        if (trim((string)($data['image_path'] ?? '')) === '') {
            $errors['image_path'] = 'Image path is required.';
        }

        if (trim((string)($data['story_type'] ?? '')) === '') {
            $errors['story_type'] = 'Story type is required.';
        }

        if (trim((string)($data['age'] ?? '')) === '') {
            $errors['age'] = 'Age is required.';
        }

        if (trim((string)($data['language'] ?? '')) === '') {
            $errors['language'] = 'Language is required.';
        }

        if ((int)($data['event_id'] ?? 0) <= 0) {
            $errors['event_id'] = 'Event ID is required.';
        }

        return $errors;
    }
}