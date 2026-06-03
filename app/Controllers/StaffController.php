<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Studio;
use App\Models\Usuario;

class StaffController extends Controller
{
    private const ALLOWED_ROLES = ['admin', 'staff'];

    // ── helpers ──────────────────────────────────────────────────────────

    private function planLimit(): array
    {
        $sid    = Auth::studioId();
        $studio = (new Studio())->find($sid);
        $planDef = Studio::PLANS[$studio['plan'] ?? 'free'] ?? Studio::PLANS['free'];
        return [
            'studioId' => $sid,
            'max'      => $planDef['max_usuarios'],
            'count'    => (new Usuario())->countByStudio($sid),
            'plan'     => $studio['plan'] ?? 'free',
        ];
    }

    // ── GET /staff ────────────────────────────────────────────────────────

    public function index(array $params = []): void
    {
        $sid   = Auth::studioId();
        $limit = $this->planLimit();

        $this->render('staff.index', [
            'pageTitle'    => __('staff.title'),
            'users'        => (new Usuario())->allByStudio($sid),
            'maxUsers'     => $limit['max'],
            'limitReached' => $limit['count'] >= $limit['max'],
            'plan'         => $limit['plan'],
            'currentId'    => (int) (Auth::user()['id'] ?? 0),
        ]);
    }

    // ── GET /staff/nuevo ──────────────────────────────────────────────────

    public function nuevo(array $params = []): void
    {
        $limit = $this->planLimit();
        if ($limit['count'] >= $limit['max']) {
            $this->flash('error', __('staff.limit_reached'));
            $this->redirect('staff');
        }

        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        $this->render('staff.form', [
            'pageTitle' => __('staff.new'),
            'old'       => $old,
            'editing'   => null,
        ]);
    }

    // ── POST /staff/nuevo ─────────────────────────────────────────────────

    public function guardar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', __('settings.csrf_error'));
            $this->redirect('staff');
        }

        $limit = $this->planLimit();
        if ($limit['count'] >= $limit['max']) {
            $this->flash('error', __('staff.limit_reached'));
            $this->redirect('staff');
        }

        $nombre  = trim($_POST['nombre']          ?? '');
        $email   = strtolower(trim($_POST['email'] ?? ''));
        $pwd     = $_POST['password']             ?? '';
        $confirm = $_POST['password_confirm']     ?? '';
        $rol     = $_POST['rol']                  ?? 'staff';

        $errors = [];
        if ($nombre === '')                                $errors[] = __('staff.name_required');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors[] = __('staff.email_invalid');
        if (strlen($pwd) < 8)                              $errors[] = __('staff.password_min');
        if ($pwd !== $confirm)                             $errors[] = __('staff.passwords_mismatch');
        if (!in_array($rol, self::ALLOWED_ROLES, true))    $errors[] = __('staff.role_invalid');

        $userModel = new Usuario();
        if (!$errors && $userModel->findByEmail($email)) {
            $errors[] = __('staff.email_taken');
        }

        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $_SESSION['old'] = $_POST;
            $this->redirect('staff/nuevo');
        }

        $userModel->create($nombre, $email, $pwd, $limit['studioId'], $rol);
        $this->flash('success', __('staff.created'));
        $this->redirect('staff');
    }

    // ── GET /staff/{id}/editar ────────────────────────────────────────────

    public function editar(array $params = []): void
    {
        $sid    = Auth::studioId();
        $target = (new Usuario())->findById((int) ($params['id'] ?? 0));

        if (!$target || (int) $target['studio_id'] !== $sid || $target['rol'] === 'owner') {
            $this->redirect('staff');
        }

        $this->render('staff.form', [
            'pageTitle' => __('staff.edit'),
            'old'       => [],
            'editing'   => $target,
        ]);
    }

    // ── POST /staff/{id}/editar ───────────────────────────────────────────

    public function actualizar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', __('settings.csrf_error'));
            $this->redirect('staff');
        }

        $sid    = Auth::studioId();
        $uid    = (int) ($params['id'] ?? 0);
        $target = (new Usuario())->findById($uid);

        if (!$target || (int) $target['studio_id'] !== $sid || $target['rol'] === 'owner') {
            $this->redirect('staff');
        }

        $rol = $_POST['rol'] ?? 'staff';
        if (!in_array($rol, self::ALLOWED_ROLES, true)) {
            $this->flash('error', __('staff.role_invalid'));
            $this->redirect("staff/{$uid}/editar");
        }

        (new Usuario())->updateRole($uid, $sid, $rol);
        $this->flash('success', __('staff.updated'));
        $this->redirect('staff');
    }

    // ── POST /staff/{id}/borrar ───────────────────────────────────────────

    public function borrar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', __('settings.csrf_error'));
            $this->redirect('staff');
        }

        $sid       = Auth::studioId();
        $uid       = (int) ($params['id'] ?? 0);
        $currentId = (int) (Auth::user()['id'] ?? 0);

        if ($uid === $currentId) {
            $this->flash('error', __('staff.cant_delete_self'));
            $this->redirect('staff');
        }

        $target = (new Usuario())->findById($uid);

        if (!$target || (int) $target['studio_id'] !== $sid) {
            $this->redirect('staff');
        }
        if ($target['rol'] === 'owner') {
            $this->flash('error', __('staff.cant_delete_owner'));
            $this->redirect('staff');
        }

        (new Usuario())->deleteFromStudio($uid, $sid);
        $this->flash('success', __('staff.deleted'));
        $this->redirect('staff');
    }
}
