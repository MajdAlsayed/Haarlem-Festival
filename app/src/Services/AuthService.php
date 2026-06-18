<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\AuthServiceInterface;
use App\Models\User;
use App\Repositories\PasswordResetTokenRepository;
use App\Repositories\UserRepository;

final class AuthService implements AuthServiceInterface
{
    private const PASSWORD_RESET_TTL_SECONDS = 3600;

    private UserRepository $users;
    private PasswordResetTokenRepository $passwordResetTokens;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->passwordResetTokens = new PasswordResetTokenRepository();
    }

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
        if (strlen($password) < 12) return false;
        if (!preg_match('/[a-z]/', $password)) return false;
        if (!preg_match('/[A-Z]/', $password)) return false;
        if (!preg_match('/\d/', $password)) return false;
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) return false;

        return true;
    }

    public function attemptLogin(string $emailOrUsername, string $password): ?User
    {
        $identifier = trim($emailOrUsername);
        if ($identifier === '') return null;

        $user = $this->findUserByIdentifier($identifier);

        if ($user === null || !$user->isActive) return null;
        if (!$this->verifyPassword($password, $user->passwordHash)) return null;

        return $user;
    }

    public function register(
        string $username,
        string $email,
        string $password,
        string $passwordConfirm,
        string $firstName,
        string $lastName
    ): array {
        $error = $this->validateRegistrationInput($username, $email, $password, $passwordConfirm, $firstName, $lastName);
        if ($error !== null) {
            return ['ok' => false, 'error' => $error];
        }

        if ($this->users->existsEmail($email)) {
            return ['ok' => false, 'error' => 'Email already in use.'];
        }

        if ($this->users->existsUsername($username)) {
            return ['ok' => false, 'error' => 'Username already in use.'];
        }

        $roleId = $this->users->getRoleIdByName('customer') ?? 2;
        $userId = $this->users->createUser($roleId, $username, $email, $this->hashPassword($password), $firstName, $lastName);

        return ['ok' => true, 'user_id' => $userId, 'role_id' => $roleId];
    }

    public function createPasswordResetRequest(string $identifier): array
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return ['ok' => false, 'error' => 'Please enter your email or username.'];
        }

        $user = $this->findUserByIdentifier($identifier);

        if ($user === null || !$user->isActive) {
            return ['ok' => false, 'error' => 'No active account was found with that email or username.'];
        }

        $token = $this->issuePasswordResetToken($user->id);

        return [
            'ok'         => true,
            'message'    => 'Reset link created successfully.',
            'dummy_link' => '/reset-password?token=' . urlencode($token),
        ];
    }

    public function validatePasswordResetToken(string $token): bool
    {
        return $this->findValidResetByToken(trim($token)) !== null;
    }

    public function resetPassword(string $token, string $password, string $passwordConfirm): array
    {
        $reset = $this->findValidResetByToken(trim($token));
        if ($reset === null) {
            return ['ok' => false, 'error' => 'Invalid or expired reset link.'];
        }

        $passwordError = $this->validateNewPassword($password, $passwordConfirm);
        if ($passwordError !== null) {
            return ['ok' => false, 'error' => $passwordError];
        }

        $this->applyPasswordReset((int)$reset['user_id'], (int)$reset['reset_id'], $this->hashPassword($password));

        return ['ok' => true, 'message' => 'Your password has been reset. Please log in with your new password.'];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function findUserByIdentifier(string $identifier): ?User
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $this->users->findByEmail(strtolower($identifier));
        }
        return $this->users->findByUsername($identifier);
    }

    private function findValidResetByToken(string $token): ?array
    {
        $reset = $this->passwordResetTokens->findValidByTokenHash(hash('sha256', $token));
        return ($reset !== null && (bool)$reset['is_active']) ? $reset : null;
    }

    private function validateRegistrationInput(
        string $username,
        string $email,
        string $password,
        string $passwordConfirm,
        string $firstName,
        string $lastName
    ): ?string {
        if ($username === '' || $email === '' || $password === '' || $firstName === '' || $lastName === '') {
            return 'Please fill in all fields.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Please use a valid email.';
        }
        return $this->validateNewPassword($password, $passwordConfirm);
    }

    private function validateNewPassword(string $password, string $passwordConfirm): ?string
    {
        if ($password !== $passwordConfirm) {
            return 'Passwords do not match.';
        }
        if (!$this->passwordStrongEnough($password)) {
            return 'Password must be 12+ chars with upper, lower, number, symbol.';
        }
        return null;
    }

    private function issuePasswordResetToken(int $userId): string
    {
        $token     = bin2hex(random_bytes(32));
        $expiresAt = gmdate('Y-m-d H:i:s', time() + self::PASSWORD_RESET_TTL_SECONDS);

        $this->passwordResetTokens->invalidateAllForUser($userId);
        $this->passwordResetTokens->create($userId, hash('sha256', $token), $expiresAt);

        return $token;
    }

    private function applyPasswordReset(int $userId, int $resetId, string $passwordHash): void
    {
        $this->users->updatePasswordHash($userId, $passwordHash);
        $this->passwordResetTokens->markUsed($resetId);
        $this->passwordResetTokens->invalidateAllForUser($userId);
    }
}