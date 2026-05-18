<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function showLogin(array $params = []): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        $this->render('login', [], null);
    }

    public function login(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de seguridad inválido. Intentá de nuevo.');
            $this->redirect('login');
        }

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->flash('error', 'Completá todos los campos.');
            $_SESSION['old'] = ['email' => $email];
            $this->redirect('login');
        }

        $model = new Usuario();
        $user  = $model->findByEmail($email);

        if (!$user || !$model->verifyPassword($password, $user['password_hash'])) {
            $this->flash('error', 'Email o contraseña incorrectos.');
            $_SESSION['old'] = ['email' => $email];
            $this->redirect('login');
        }

        Auth::login($user);
        unset($_SESSION['old']);
        $this->redirect('dashboard');
    }

    public function logout(array $params = []): void
    {
        Auth::logout();
        $this->redirect('login');
    }
}
