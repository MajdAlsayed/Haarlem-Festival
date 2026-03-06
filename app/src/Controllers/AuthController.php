<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\PasswordResetTokenRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\ViewModels\ForgotPasswordViewModel;
use App\ViewModels\LoginViewModel;
use App\ViewModels\RegisterViewModel;
use App\ViewModels\ResetPasswordViewModel;

final class AuthController
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService(
            new UserRepository(),
            new PasswordResetTokenRepository()
        );
    }

    // ── Login ─────────────────────────────────────────────────────

    public function showLogin(): void
    {
        $viewModel = new LoginViewModel(
            csrf:  Csrf::token('login'),
            error: Session::getFlash('login_error'),
        );

        require __DIR__ . '/../Views/Authentication/Login.php';
    }

    public function login(): void
    {
        if (!Csrf::validate('login', $_POST['_csrf'] ?? null)) {
            Session::setFlash('login_error', 'Invalid credentials.');
            header('Location: /login');
            exit;
        }

        $identifier = trim((string)($_POST['identifier'] ?? ''));
        $password = (string)($_POST['password']   ?? '');

        $user = $this->auth->attemptLogin($identifier, $password);
        if ($user === null) {
            Session::setFlash('login_error', 'Invalid credentials.');
            header('Location: /login');
            exit;
        }

        Session::regenerate();
        $_SESSION['auth'] = [
            'user_id'  => $user->id,
            'email'    => $user->email,
            'role_id'  => $user->roleId,
            'username' => $user->username,
        ];

        header('Location: /');
        exit;
    }

    // ── Logout ────────────────────────────────────────────────────

    public function logout(): void
    {
        unset($_SESSION['auth']);
        Session::regenerate();

        header('Location: /');
        exit;
    }

    // ── Register ──────────────────────────────────────────────────

    public function showRegister(): void
    {
        $old = json_decode(Session::getFlash('register_old') ?? '{}', true) ?? [];

        $viewModel = new RegisterViewModel(
            csrf:            Csrf::token('register'),
            captchaQuestion: $this->auth->newCaptchaQuestion(),
            error:           Session::getFlash('register_error'),
            username:        (string)($old['username']  ?? ''),
            email:           (string)($old['email']     ?? ''),
            firstName:       (string)($old['firstName'] ?? ''),
            lastName:        (string)($old['lastName']  ?? ''),
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

        // Capture all input first
        $username        = trim((string)($_POST['username']         ?? ''));
        $email           = trim((string)($_POST['email']            ?? ''));
        $password        = (string)($_POST['password']              ?? '');
        $passwordConfirm = (string)($_POST['password_confirm']      ?? '');
        $firstName       = trim((string)($_POST['first_name']       ?? ''));
        $lastName        = trim((string)($_POST['last_name']        ?? ''));

        // Store old values in case we need to repopulate
        $old = json_encode([
            'username'  => $username,
            'email'     => $email,
            'firstName' => $firstName,
            'lastName'  => $lastName,
        ]);

        if (!$this->auth->validateCaptcha($_POST['captcha'] ?? null)) {
            Session::setFlash('register_error', 'CAPTCHA incorrect.');
            Session::setFlash('register_old', $old);
            header('Location: /register');
            exit;
        }

        $result = $this->auth->register(
            username:        $username,
            email:           $email,
            password:        $password,
            passwordConfirm: $passwordConfirm,
            firstName:       $firstName,
            lastName:        $lastName,
        );

        if (!$result['ok']) {
            Session::setFlash('register_error', $result['error']);
            Session::setFlash('register_old', $old);
            header('Location: /register');
            exit;
        }

        Session::regenerate();
        $_SESSION['auth'] = [
            'user_id'  => $result['user_id'],
            'email'    => $email,
            'role_id'  => $result['role_id'],
            'username' => $username,
        ];

        header('Location: /');
        exit;
    }
}