<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Studio;
use App\Models\Usuario;

class StudiosController extends Controller
{
    /** GET /registro */
    public function registro(array $params = []): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);
        $this->render('studios.register', ['old' => $old], null);
    }

    /** POST /registro */
    public function guardarRegistro(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido. Intentá de nuevo.');
            $this->redirect('registro');
        }

        $studioNombre = trim($_POST['studio_nombre'] ?? '');
        $userName     = trim($_POST['nombre']        ?? '');
        $email        = strtolower(trim($_POST['email']    ?? ''));
        $password     = $_POST['password']            ?? '';
        $passwordConf = $_POST['password_confirm']    ?? '';

        $errors = [];
        if ($studioNombre === '')          $errors[] = 'El nombre del estudio es obligatorio.';
        if (mb_strlen($studioNombre) > 100) $errors[] = 'El nombre del estudio no puede superar 100 caracteres.';
        if ($userName === '')              $errors[] = 'Tu nombre es obligatorio.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El email no es válido.';
        if (strlen($password) < 8)        $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        if ($password !== $passwordConf)  $errors[] = 'Las contraseñas no coinciden.';

        if (!$errors) {
            $db   = Database::get();
            $stmt = $db->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetchColumn()) {
                $errors[] = 'Ya existe una cuenta con ese email.';
            }
        }

        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $_SESSION['old'] = $_POST;
            $this->redirect('registro');
        }

        // ── Create studio ─────────────────────────────────────────────────
        $studioModel = new Studio();
        $slug        = $studioModel->uniqueSlug(Studio::slugify($studioNombre));
        $studioId    = $studioModel->crear($studioNombre, $slug, 0); // owner set after user

        // ── Create owner user ─────────────────────────────────────────────
        $userModel = new Usuario();
        $userId    = $userModel->create($userName, $email, $password, $studioId, 'owner');

        // ── Assign owner to studio ────────────────────────────────────────
        $db = Database::get();
        $db->prepare('UPDATE studios SET owner_id = ? WHERE id = ?')->execute([$userId, $studioId]);

        // ── Auto-login ────────────────────────────────────────────────────
        Auth::login([
            'id'        => $userId,
            'nombre'    => $userName,
            'email'     => $email,
            'rol'       => 'owner',
            'studio_id' => $studioId,
        ]);

        unset($_SESSION['old']);
        $this->flash('success', '¡Estudio creado! Bienvenido a InkManager.');
        $this->redirect('dashboard');
    }
}
