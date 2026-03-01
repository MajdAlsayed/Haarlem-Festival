<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

final class AuthService
{
    public function __construct(private UserRepository $users) {}

    // ── Password ─────────────────────────────────────────────────

    public function hashPassword(string $password): string
    {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        $hash = password_hash($password, $algo);
        if ($hash === false) {
            throw new \RuntimeException('Password hashing failed.');
        }
        return $hash;
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function passwordStrongEnough(string $password): bool
    {
        if (strlen($password) < 12)                    return false;
        if (!preg_match('/[a-z]/', $password))         return false;
        if (!preg_match('/[A-Z]/', $password))         return false;
        if (!preg_match('/\d/', $password))            return false;
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) return false;
        return true;
    }

    // ── Login ─────────────────────────────────────────────────────

    /**
     * Returns a User model on success, null on any failure.
     * Never reveals whether the email exists or not.
     */
    public function attemptLogin(string $emailOrUsername, string $password): ?User
{
    $identifier = trim($emailOrUsername);
    if ($identifier === '') return null;

    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $user = $this->users->findByEmail(strtolower($identifier));
    } else {
        $user = $this->users->findByUsername($identifier);
    }

    if ($user === null)                                        return null;
    if (!$user->isActive)                                      return null;
    if (!$this->verifyPassword($password, $user->passwordHash)) return null;

    return $user;
}

    // ── Registration ──────────────────────────────────────────────

    /**
     * Returns ['ok' => true,  'user_id' => int, 'role_id' => int]
     * or      ['ok' => false, 'error'   => string]
     */
    public function register(
        string $username,
        string $email,
        string $password,
        string $passwordConfirm,
        string $firstName,
        string $lastName
    ): array {

        if ($username === '' || $email === '' || $password === '' || $firstName === '' || $lastName === '') {
            return ['ok' => false, 'error' => 'Please fill in all fields.'];
        }

        if ($password !== $passwordConfirm) {
            return ['ok' => false, 'error' => 'Passwords do not match.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Please use a valid email.'];
        }

        if (!$this->passwordStrongEnough($password)) {
            return ['ok' => false, 'error' => 'Password must be 12+ chars with upper, lower, number, symbol.'];
        }

        if ($this->users->existsEmail($email)) {
            return ['ok' => false, 'error' => 'Email already in use.'];
        }

        if ($this->users->existsUsername($username)) {
            return ['ok' => false, 'error' => 'Username already in use.'];
        }

        $roleId = $this->users->getRoleIdByName('customer') ?? 2;
        $hash   = $this->hashPassword($password);
        $userId = $this->users->createUser($roleId, $username, $email, $hash, $firstName, $lastName);

        return ['ok' => true, 'user_id' => $userId, 'role_id' => $roleId];
    }

    // ── CAPTCHA ───────────────────────────────────────────────────

    public function newCaptchaQuestion(): string
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        $_SESSION['_captcha'] = [
            'answer' => (string)($a + $b),
            'ts'     => time(),
        ];

        return "What is {$a} + {$b}?";
    }

    public function validateCaptcha(mixed $input): bool
    {
        $data = $_SESSION['_captcha'] ?? null;
        unset($_SESSION['_captcha']);

        if (!is_array($data) || !isset($data['answer'], $data['ts'])) return false;
        if (time() - (int)$data['ts'] > 600)                          return false;

        $given = trim((string)$input);
        return $given !== '' && hash_equals((string)$data['answer'], $given);
    }
}