<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\ServiceInterface\AuthServiceInterface;
use App\Core\Csrf;
use App\Core\Session;
use App\Services\AuthService;
use App\ViewModels\ForgotPasswordViewModel;
use App\ViewModels\LoginViewModel;
use App\ViewModels\RegisterViewModel;
use App\ViewModels\ResetPasswordViewModel;

final class AuthController
{
    private AuthServiceInterface $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    public function showLogin(): void
    {
        $returnTo = isset($_GET['return']) ? trim((string)$_GET['return']) : '';
        if ($returnTo === '' || !str_starts_with($returnTo, '/')) {
            $returnTo = null;
        }

        $viewModel = new LoginViewModel(
            csrf: Csrf::token('login'),
            error: Session::getFlash('login_error'),
            returnTo: $returnTo,
        );

        require __DIR__ . '/../Views/Authentication/Login.php';
    }

    public function login(): void
    {
        if (!Csrf::validate('login', $_POST['_csrf'] ?? null)) {
            $this->redirectToLoginWithError('Invalid credentials.');
        }

        $identifier = trim((string)($_POST['identifier'] ?? ''));
        $password   = (string)($_POST['password'] ?? '');

        try {
            $user = $this->auth->attemptLogin($identifier, $password);
        } catch (\Throwable) {
            $this->redirectToLoginWithError('An error occurred. Please try again.');
        }

        if ($user === null) {
            $this->redirectToLoginWithError('Invalid credentials.');
        }

        $this->startAuthSession($user->id, $user->email, $user->roleId, $user->username);

        $return = trim((string)($_POST['return'] ?? ''));
        header('Location: ' . ($return !== '' && str_starts_with($return, '/') ? $return : '/'));
        exit;
    }

    public function logout(): void
    {
        unset($_SESSION['auth']);
        Session::regenerate();

        header('Location: /');
        exit;
    }

    public function showRegister(): void
    {
        $old       = json_decode(Session::getFlash('register_old') ?? '{}', true) ?? [];
        $appConfig = require __DIR__ . '/../Config/app.php';

        $viewModel = new RegisterViewModel(
            csrf: Csrf::token('register'),
            recaptchaSiteKey: $appConfig['recaptcha_site_key'],
            error: Session::getFlash('register_error'),
            username: (string)($old['username'] ?? ''),
            email: (string)($old['email'] ?? ''),
            firstName: (string)($old['firstName'] ?? ''),
            lastName: (string)($old['lastName'] ?? ''),
        );

        require __DIR__ . '/../Views/Authentication/Register.php';
    }

    public function register(): void
    {
        if (!Csrf::validate('register', $_POST['_csrf'] ?? null)) {
            Session::setFlash('register_error', 'Something went wrong. Please try again.');
            header('Location: /register');
            exit;
        }

        $input = $this->extractRegisterInput();
        $old   = $this->buildOldPayload($input);

        if (!$this->verifyRecaptcha((string)($_POST['g-recaptcha-response'] ?? ''))) {
            $this->redirectToRegisterWithError('Please complete the CAPTCHA.', $old);
        }

        try {
            $result = $this->auth->register(
                username:        $input['username'],
                email:           $input['email'],
                password:        $input['password'],
                passwordConfirm: $input['passwordConfirm'],
                firstName:       $input['firstName'],
                lastName:        $input['lastName'],
            );
        } catch (\Throwable) {
            $this->redirectToRegisterWithError('An error occurred. Please try again.', $old);
        }

        if (!$result['ok']) {
            $this->redirectToRegisterWithError($result['error'], $old);
        }

        $this->startAuthSession($result['user_id'], $input['email'], $result['role_id'], $input['username']);

        header('Location: /');
        exit;
    }

    public function showForgotPassword(): void
    {
        $viewModel = new ForgotPasswordViewModel(
            csrf: Csrf::token('forgot_password'),
            error: Session::getFlash('forgot_password_error'),
            success: Session::getFlash('forgot_password_success'),
            dummyLink: Session::getFlash('forgot_password_dummy_link'),
            identifier: Session::getFlash('forgot_password_old_identifier') ?? '',
        );

        require __DIR__ . '/../Views/Authentication/ForgotPassword.php';
    }

    public function forgotPassword(): void
    {
        if (!Csrf::validate('forgot_password', $_POST['_csrf'] ?? null)) {
            $this->redirectToForgotPasswordWithError('Something went wrong. Please try again.');
        }

        $identifier = trim((string)($_POST['identifier'] ?? ''));
        Session::setFlash('forgot_password_old_identifier', $identifier);

        try {
            $result = $this->auth->createPasswordResetRequest($identifier);
        } catch (\Throwable) {
            $this->redirectToForgotPasswordWithError('An error occurred. Please try again.');
        }

        if (!$result['ok']) {
            $this->redirectToForgotPasswordWithError($result['error']);
        }

        Session::setFlash('forgot_password_success', $result['message']);
        Session::setFlash('forgot_password_dummy_link', $result['dummy_link']);
        header('Location: /forgot-password');
        exit;
    }

    public function showResetPassword(): void
    {
        $token   = trim((string)($_GET['token'] ?? ''));
        $error   = Session::getFlash('reset_password_error');
        $success = Session::getFlash('reset_password_success');

        try {
            if ($success === null && !$this->auth->validatePasswordResetToken($token)) {
                $error = $error ?? 'Invalid or expired reset link.';
            }
        } catch (\Throwable) {
            $error = 'An error occurred. Please try again.';
        }

        $viewModel = new ResetPasswordViewModel(
            csrf: Csrf::token('reset_password'),
            token: $token,
            error: $error,
            success: $success,
        );

        require __DIR__ . '/../Views/Authentication/ResetPassword.php';
    }

    public function resetPassword(): void
    {
        if (!Csrf::validate('reset_password', $_POST['_csrf'] ?? null)) {
            Session::setFlash('reset_password_error', 'Something went wrong. Please try again.');
            header('Location: /login');
            exit;
        }

        $token = trim((string)($_POST['token'] ?? ''));

        try {
            $result = $this->auth->resetPassword(
                $token,
                (string)($_POST['password'] ?? ''),
                (string)($_POST['password_confirm'] ?? ''),
            );
        } catch (\Throwable) {
            $this->redirectToResetPasswordWithError('An error occurred. Please try again.', $token);
        }

        if (!$result['ok']) {
            $this->redirectToResetPasswordWithError($result['error'], $token);
        }

        Session::setFlash('reset_password_success', $result['message']);
        header('Location: /reset-password?token=' . urlencode($token));
        exit;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function extractRegisterInput(): array
    {
        return [
            'username'        => trim((string)($_POST['username'] ?? '')),
            'email'           => trim((string)($_POST['email'] ?? '')),
            'password'        => (string)($_POST['password'] ?? ''),
            'passwordConfirm' => (string)($_POST['password_confirm'] ?? ''),
            'firstName'       => trim((string)($_POST['first_name'] ?? '')),
            'lastName'        => trim((string)($_POST['last_name'] ?? '')),
        ];
    }

    private function buildOldPayload(array $input): string
    {
        return (string)json_encode([
            'username'  => $input['username'],
            'email'     => $input['email'],
            'firstName' => $input['firstName'],
            'lastName'  => $input['lastName'],
        ]);
    }

    private function startAuthSession(mixed $userId, string $email, mixed $roleId, string $username): void
    {
        Session::regenerate();
        $_SESSION['auth'] = [
            'user_id'  => $userId,
            'email'    => $email,
            'role_id'  => $roleId,
            'username' => $username,
        ];
    }

    private function redirectToLoginWithError(string $error): never
    {
        Session::setFlash('login_error', $error);
        header('Location: /login');
        exit;
    }

    private function redirectToRegisterWithError(string $error, string $old): never
    {
        Session::setFlash('register_error', $error);
        Session::setFlash('register_old', $old);
        header('Location: /register');
        exit;
    }

    private function redirectToForgotPasswordWithError(string $error): never
    {
        Session::setFlash('forgot_password_error', $error);
        header('Location: /forgot-password');
        exit;
    }

    private function redirectToResetPasswordWithError(string $error, string $token): never
    {
        Session::setFlash('reset_password_error', $error);
        header('Location: /reset-password?token=' . urlencode($token));
        exit;
    }

    private function verifyRecaptcha(string $token): bool
    {
        if ($token === '') return false;

        $appConfig = require __DIR__ . '/../Config/app.php';
        $body = http_build_query([
            'secret'   => $appConfig['recaptcha_secret_key'],
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $body,
            'timeout' => 5,
        ]]);

        $raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
        if ($raw === false) return false;

        $data = json_decode($raw, true);
        return isset($data['success']) && $data['success'] === true;
    }
}