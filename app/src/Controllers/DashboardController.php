<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Models\User;

final class DashboardController
{
    public function index(): void
    {
        $id = $_SESSION['user_id'] ?? null;
        if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
            View::redirect('/');
        }

        $user = User::findById((int) $id);
        if ($user === null) {
            unset($_SESSION['user_id']);
            View::redirect('/');
        }

        View::render('dashboard', [
            'title' => 'Inicio',
            'user' => $user,
            'csrf' => Csrf::token(),
        ]);
    }
}
