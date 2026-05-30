<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\PasswordResetTokenRepository;
use App\Repositories\UserRepository;

final class AuthService
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

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = $this->users->findByEmail(strtolower($identifier));
        } else {
            $user = $this->users->findByUsername($identifier);
        }

        if ($user === null) return null;
        if (!$user->isActive) return null;
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

    public function createPasswordResetRequest(string $identifier): array
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return [
                'ok' => false,
                'error' => 'Please enter your email or username.'
            ];
        }

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = $this->users->findByEmail(strtolower($identifier));
        } else {
            $user = $this->users->findByUsername($identifier);
        }

        if ($user === null || !$user->isActive) {
            return [
                'ok' => false,
                'error' => 'No active account was found with that email or username.'
            ];
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = gmdate('Y-m-d H:i:s', time() + self::PASSWORD_RESET_TTL_SECONDS);

        $this->passwordResetTokens->invalidateAllForUser($user->id);
        $this->passwordResetTokens->create($user->id, $tokenHash, $expiresAt);

        $dummyLink = '/reset-password?token=' . urlencode($token);

        return [
            'ok' => true,
            'message' => 'Reset link created successfully.',
            'dummy_link' => $dummyLink,
        ];
    }

    public function validatePasswordResetToken(string $token): bool
    {
        $token = trim($token);
        if ($token === '') {
            return false;
        }

        $reset = $this->passwordResetTokens->findValidByTokenHash(hash('sha256', $token));

        return $reset !== null && (bool)$reset['is_active'];
    }

    public function resetPassword(string $token, string $password, string $passwordConfirm): array
    {
        $token = trim($token);

        if ($token === '') {
            return ['ok' => false, 'error' => 'Invalid or expired reset link.'];
        }

        $reset = $this->passwordResetTokens->findValidByTokenHash(hash('sha256', $token));
        if ($reset === null || !(bool)$reset['is_active']) {
            return ['ok' => false, 'error' => 'Invalid or expired reset link.'];
        }

        if ($password !== $passwordConfirm) {
            return ['ok' => false, 'error' => 'Passwords do not match.'];
        }

        if (!$this->passwordStrongEnough($password)) {
            return ['ok' => false, 'error' => 'Password must be 12+ chars with upper, lower, number, symbol.'];
        }

        $passwordHash = $this->hashPassword($password);
        $userId = (int)$reset['user_id'];
        $resetId = (int)$reset['reset_id'];

        $this->users->updatePasswordHash($userId, $passwordHash);
        $this->passwordResetTokens->markUsed($resetId);
        $this->passwordResetTokens->invalidateAllForUser($userId);

        return ['ok' => true, 'message' => 'Your password has been reset. Please log in with your new password.'];
    }

}