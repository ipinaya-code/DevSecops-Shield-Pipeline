<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Models\User;

final class AuthController
{
    public function showLogin(): void
    {
        if ($this->currentUserId() !== null) {
            View::redirect('/dashboard');
        }

        View::render('auth/login', [
            'title' => 'Entrar',
            'error' => $this->pullFlash('error'),
            'success' => $this->pullFlash('success'),
            'oldEmail' => $_SESSION['_old']['email'] ?? '',
            'csrf' => Csrf::token(),
        ]);
        unset($_SESSION['_old']);
    }

    public function showRegister(): void
    {
        if ($this->currentUserId() !== null) {
            View::redirect('/dashboard');
        }

        View::render('auth/register', [
            'title' => 'Crear cuenta',
            'error' => $this->pullFlash('error'),
            'old' => $_SESSION['_old'] ?? [],
            'csrf' => Csrf::token(),
        ]);
        unset($_SESSION['_old']);
    }

    public function register(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $this->flash('error', 'La sesión expiró. Inténtalo de nuevo.');
            View::redirect('/register');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');
        $_SESSION['_old'] = ['name' => $name, 'email' => $email];

        if ($name === '' || mb_strlen($name) < 2) {
            $this->flash('error', 'Escribe tu nombre (mínimo 2 caracteres).');
            View::redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'El correo no es válido.');
            View::redirect('/register');
        }

        if (strlen($password) < 8) {
            $this->flash('error', 'La contraseña debe tener al menos 8 caracteres.');
            View::redirect('/register');
        }

        if ($password !== $confirm) {
            $this->flash('error', 'Las contraseñas no coinciden.');
            View::redirect('/register');
        }

        if (User::findByEmail($email) !== null) {
            $this->flash('error', 'Ese correo ya tiene cuenta. Entra con tu usuario.');
            View::redirect('/login');
        }

        $id = User::create($name, $email, $password);
        $_SESSION['user_id'] = $id;
        unset($_SESSION['_old']);
        View::redirect('/dashboard');
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $this->flash('error', 'La sesión expiró. Inténtalo de nuevo.');
            View::redirect('/');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $_SESSION['_old'] = ['email' => $email];

        $user = User::findByEmail($email);
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            $this->flash('error', 'Correo o contraseña incorrectos.');
            View::redirect('/');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        unset($_SESSION['_old']);
        View::redirect('/dashboard');
    }

    public function logout(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            View::redirect('/dashboard');
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        session_start();
        $this->flash('success', 'Sesión cerrada. Puedes volver a entrar cuando quieras.');
        View::redirect('/');
    }

    private function currentUserId(): ?int
    {
        $id = $_SESSION['user_id'] ?? null;
        return is_int($id) || (is_string($id) && ctype_digit($id)) ? (int) $id : null;
    }

    private function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    private function pullFlash(string $key): ?string
    {
        $message = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return is_string($message) ? $message : null;
    }
}
