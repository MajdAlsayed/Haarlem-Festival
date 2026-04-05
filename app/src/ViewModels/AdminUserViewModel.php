<?php

declare(strict_types=1);

namespace App\ViewModels;

final class AdminUserViewModel
{
    public string $search;
    public string $sortBy;
    public string $sortDir;

    public function __construct(
        public array $users,
        string       $search = '',
        string       $sortBy = 'created_at',
        string       $sortDir = 'DESC',
    ) {
        $allowed = ['first_name', 'email', 'created_at'];
        $this->search = $search;
        $this->sortBy = in_array($sortBy, $allowed, true) ? $sortBy : 'created_at';
        $this->sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';
    }

    // Clicking the same column again flips the direction
    public function sortLink(string $col): string
    {
        $nextDir = ($this->sortBy === $col && $this->sortDir === 'ASC') ? 'DESC' : 'ASC';
        return '/admin/users?sort=' . rawurlencode($col)
            . '&dir=' . $nextDir
            . '&search=' . rawurlencode($this->search);
    }

    // Returns empty string if this column is not the active sort
    public function sortArrow(string $col): string
    {
        if ($this->sortBy !== $col){
            return '';
        }
        return $this->sortDir === 'ASC' ? ' ↑' : ' ↓';
    }
}