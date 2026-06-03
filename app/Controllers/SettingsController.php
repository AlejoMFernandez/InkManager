<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Studio;
use App\Models\Usuario;

class SettingsController extends Controller
{
    private const TABS = ['studio', 'perfil', 'password'];

    /** GET /configuracion */
    public function index(array $params = []): void
    {
        $sid  = Auth::studioId();
        $tab  = $_GET['tab'] ?? 'studio';
        if (!in_array($tab, self::TABS, true)) $tab = 'studio';

        $studioModel = new Studio();
        $studio      = $studioModel->find($sid);
        $clientCount = $studioModel->contarClientes($sid);

        $this->render('settings.index', [
            'pageTitle'   => __('settings.title'),
            'studio'      => $studio,
            'user'        => Auth::user(),
            'activeTab'   => $tab,
            'clientCount' => $clientCount,
        ]);
    }

    /** POST /configuracion/estudio */
    public function updateStudio(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', __('settings.csrf_error'));
            $this->redirect('configuracion?tab=studio');
        }

        $sid  = Auth::studioId();
        $data = [
            'nombre'    => trim($_POST['nombre']    ?? ''),
            'telefono'  => trim($_POST['telefono']  ?? ''),
            'instagram' => ltrim(trim($_POST['instagram'] ?? ''), '@'),
            'website'   => trim($_POST['website']   ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
        ];

        if ($data['nombre'] === '') {
            $this->flash('error', __('settings.studio_name_required'));
            $this->redirect('configuracion?tab=studio');
        }
        if (mb_strlen($data['nombre']) > 100) {
            $this->flash('error', __('settings.studio_name_too_long'));
            $this->redirect('configuracion?tab=studio');
        }

        (new Studio())->updateSettings($sid, $data);

        $this->flash('success', __('settings.studio_saved'));
        $this->redirect('configuracion?tab=studio');
    }

    /** POST /configuracion/perfil */
    public function updateProfile(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', __('settings.csrf_error'));
            $this->redirect('configuracion?tab=perfil');
        }

        $uid    = (int) (Auth::user()['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $email  = strtolower(trim($_POST['email'] ?? ''));

        $errors = [];
        if ($nombre === '') $errors[] = __('settings.name_required');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = __('settings.email_invalid');

        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $this->redirect('configuracion?tab=perfil');
        }

        (new Usuario())->updateProfile($uid, $nombre, $email);

        // Keep session in sync
        $_SESSION['auth_user']['nombre'] = $nombre;
        $_SESSION['auth_user']['email']  = $email;

        $this->flash('success', __('settings.profile_saved'));
        $this->redirect('configuracion?tab=perfil');
    }

    /** POST /configuracion/password */
    public function updatePassword(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', __('settings.csrf_error'));
            $this->redirect('configuracion?tab=password');
        }

        $uid      = (int) (Auth::user()['id'] ?? 0);
        $current  = $_POST['password_actual']  ?? '';
        $new      = $_POST['password_nuevo']   ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        $userModel = new Usuario();
        $userData  = $userModel->findById($uid);

        $errors = [];
        if (!password_verify($current, $userData['password_hash'] ?? '')) {
            $errors[] = __('settings.wrong_password');
        }
        if (strlen($new) < 8)  $errors[] = __('settings.password_min');
        if ($new !== $confirm) $errors[] = __('settings.passwords_mismatch');

        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $this->redirect('configuracion?tab=password');
        }

        $userModel->updatePassword($uid, $new);
        $this->flash('success', __('settings.password_saved'));
        $this->redirect('configuracion?tab=password');
    }
}
