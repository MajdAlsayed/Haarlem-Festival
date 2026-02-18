<?php

namespace App\Validation;

use App\Exceptions\ValidationException;

class Validator
{
    /** Slug: lowercase letters, numbers, hyphens only; 1–100 chars */
    public static function validateSlug(string $slug): void
    {
        $slug = trim($slug);
        if ($slug === '') {
            throw new ValidationException('Slug cannot be empty', ['slug' => 'Slug is required']);
        }
        if (strlen($slug) > 100) {
            throw new ValidationException('Slug too long', ['slug' => 'Slug must be 100 characters or less']);
        }
        if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
            throw new ValidationException('Invalid slug format', ['slug' => 'Slug may only contain lowercase letters, numbers and hyphens']);
        }
    }

    /** Event category / type name: non-empty, reasonable length */
    public static function validateEventCategory(string $category): void
    {
        $category = trim($category);
        if ($category === '') {
            throw new ValidationException('Event category cannot be empty', ['category' => 'Category is required']);
        }
        if (strlen($category) > 50) {
            throw new ValidationException('Event category too long', ['category' => 'Category must be 50 characters or less']);
        }
    }
}
