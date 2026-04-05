<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\PasswordResetTokenRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\ViewModels\ForgotPasswordViewModel;
use App\ViewModels\LoginViewModel;
use App\ViewModels\RegisterViewModel;
use App\ViewModels\ResetPasswordViewModel;

final class AuthController
{
    private AuthService $auth;
    private SettingsRepository $settingsRepository;

    public function __construct()
    {
        $this->auth = new AuthService(
            new UserRepository(),
            new PasswordResetTokenRepository()
        );

        $this->settingsRepository = new SettingsRepository();
    }

    private function appSettings(): array
    {
        return $this->settingsRepository->getAll();
    }

    public function showLogin(): void
    {
        $returnTo = isset($_GET['return']) ? trim((string) $_GET['return']) : '';
        if ($returnTo === '' || !str_starts_with($returnTo, '/')) {
            $returnTo = null;
        }

        $viewModel = new LoginViewModel(
            csrf: Csrf::token('login'),
            error: Session::getFlash('login_error'),
            appSettings: $this->appSettings(),
            returnTo: $returnTo,
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
        $password = (string)($_POST['password'] ?? '');

        $user = $this->auth->attemptLogin($identifier, $password);

        if ($user === null) {
            Session::setFlash('login_error', 'Invalid credentials.');
            header('Location: /login');
            exit;
        }

        Session::regenerate();

        $_SESSION['auth'] = [
            'user_id' => $user->id,
            'email' => $user->email,
            'role_id' => $user->roleId,
            'username' => $user->username,
        ];

        $return = trim((string) ($_POST['return'] ?? ''));
        if ($return !== '' && str_starts_with($return, '/')) {
            header('Location: ' . $return);
            exit;
        }

        header('Location: /');
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
        $old = json_decode(Session::getFlash('register_old') ?? '{}', true) ?? [];

        $viewModel = new RegisterViewModel(
            csrf: Csrf::token('register'),
            captchaQuestion: $this->auth->newCaptchaQuestion(),
            error: Session::getFlash('register_error'),
            username: (string)($old['username'] ?? ''),
            email: (string)($old['email'] ?? ''),
            firstName: (string)($old['firstName'] ?? ''),
            lastName: (string)($old['lastName'] ?? ''),
            appSettings: $this->appSettings(),
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

        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
        $firstName = trim((string)($_POST['first_name'] ?? ''));
        $lastName = trim((string)($_POST['last_name'] ?? ''));

        $old = json_encode([
            'username' => $username,
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);

        if (!$this->auth->validateCaptcha($_POST['captcha'] ?? null)) {
            Session::setFlash('register_error', 'CAPTCHA incorrect.');
            Session::setFlash('register_old', $old);
            header('Location: /register');
            exit;
        }

        $result = $this->auth->register(
            username: $username,
            email: $email,
            password: $password,
            passwordConfirm: $passwordConfirm,
            firstName: $firstName,
            lastName: $lastName,
        );

        if (!$result['ok']) {
            Session::setFlash('register_error', $result['error']);
            Session::setFlash('register_old', $old);
            header('Location: /register');
            exit;
        }

        Session::regenerate();

        $_SESSION['auth'] = [
            'user_id' => $result['user_id'],
            'email' => $email,
            'role_id' => $result['role_id'],
            'username' => $username,
        ];

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
            appSettings: $this->appSettings(),
        );

        require __DIR__ . '/../Views/Authentication/ForgotPassword.php';
    }

    public function forgotPassword(): void
    {
        if (!Csrf::validate('forgot_password', $_POST['_csrf'] ?? null)) {
            Session::setFlash('forgot_password_error', 'Something went wrong. Please try again.');
            header('Location: /forgot-password');
            exit;
        }

        $identifier = trim((string)($_POST['identifier'] ?? ''));
        Session::setFlash('forgot_password_old_identifier', $identifier);

        $result = $this->auth->createPasswordResetRequest($identifier);

        if (!$result['ok']) {
            Session::setFlash('forgot_password_error', $result['error']);
            header('Location: /forgot-password');
            exit;
        }

        Session::setFlash('forgot_password_success', $result['message']);
        Session::setFlash('forgot_password_dummy_link', $result['dummy_link']);

        header('Location: /forgot-password');
        exit;
    }

    public function showResetPassword(): void
    {
        $token = trim((string)($_GET['token'] ?? ''));
        $error = Session::getFlash('reset_password_error');
        $success = Session::getFlash('reset_password_success');

        if ($success === null && !$this->auth->validatePasswordResetToken($token)) {
            $error = $error ?? 'Invalid or expired reset link.';
        }

        $viewModel = new ResetPasswordViewModel(
            csrf: Csrf::token('reset_password'),
            token: $token,
            error: $error,
            success: $success,
            appSettings: $this->appSettings(),
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

        $result = $this->auth->resetPassword(
            $token,
            (string)($_POST['password'] ?? ''),
            (string)($_POST['password_confirm'] ?? ''),
        );

        if (!$result['ok']) {
            Session::setFlash('reset_password_error', $result['error']);
            header('Location: /reset-password?token=' . urlencode($token));
            exit;
        }

        Session::setFlash('reset_password_success', $result['message']);
        header('Location: /reset-password?token=' . urlencode($token));
        exit;
    }
}