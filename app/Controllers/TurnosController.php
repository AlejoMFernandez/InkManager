<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Turno;
use App\Models\Cliente;

class TurnosController extends Controller
{
    /** GET /turnos — vista calendario */
    public function index(array $params = []): void
    {
        $this->render('turnos.index');
    }

    /** GET /api/turnos?start=...&end=... — feed JSON para FullCalendar */
    public function feed(array $params = []): void
    {
        $desde = $_GET['start'] ?? date('Y-m-01');
        $hasta = $_GET['end']   ?? date('Y-m-t');

        $turnos = (new Turno())->enRango($desde, $hasta);
        $events = array_map([$this, 'toCalendarEvent'], $turnos);
        $this->json($events);
    }

    /** GET /turnos/nuevo  |  GET /turnos/nuevo?cliente_id=N */
    public function nuevo(array $params = []): void
    {
        $clientes       = (new Cliente())->all('nombre', 'ASC');
        $clienteIdPre   = (int) ($_GET['cliente_id'] ?? 0);
        $fechaPreStr    = $_GET['fecha'] ?? '';
        $old            = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);
        $this->render('turnos.form', [
            'turno'       => null,
            'clientes'    => $clientes,
            'clienteIdPre'=> $clienteIdPre,
            'fechaPreStr' => $fechaPreStr,
            'old'         => $old,
            'pageTitle'   => 'Nuevo turno',
        ]);
    }

    /** POST /turnos/nuevo */
    public function guardar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) { $this->flash('error','Token inválido.'); $this->redirect('turnos/nuevo'); }

        $errors = $this->validar($_POST);
        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $_SESSION['old'] = $_POST;
            $this->redirect('turnos/nuevo');
        }

        $id = (new Turno())->crear($_POST);
        $this->flash('success', 'Turno agendado correctamente.');
        $this->redirect("turnos/{$id}");
    }

    /** GET /turnos/{id} */
    public function ver(array $params = []): void
    {
        $turno = (new Turno())->conCliente((int) $params['id']);
        if (!$turno) { $this->redirect('turnos'); return; }
        $this->render('turnos.show', compact('turno'));
    }

    /** GET /turnos/{id}/editar */
    public function editar(array $params = []): void
    {
        $turno    = (new Turno())->find((int) $params['id']);
        if (!$turno) { $this->redirect('turnos'); return; }
        $clientes = (new Cliente())->all('nombre', 'ASC');
        $old      = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);
        $this->render('turnos.form', [
            'turno'        => $turno,
            'clientes'     => $clientes,
            'clienteIdPre' => 0,
            'fechaPreStr'  => '',
            'old'          => $old,
            'pageTitle'    => 'Editar turno',
        ]);
    }

    /** POST /turnos/{id}/editar */
    public function actualizar(array $params = []): void
    {
        $id = (int) $params['id'];
        if (!Auth::verifyCsrf()) { $this->flash('error','Token inválido.'); $this->redirect("turnos/{$id}/editar"); }

        $errors = $this->validar($_POST);
        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $_SESSION['old'] = $_POST;
            $this->redirect("turnos/{$id}/editar");
        }

        (new Turno())->actualizar($id, $_POST);
        $this->flash('success', 'Turno actualizado.');
        $this->redirect("turnos/{$id}");
    }

    /** POST /turnos/{id}/borrar */
    public function borrar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) { $this->flash('error','Token inválido.'); $this->redirect('turnos'); }
        (new Turno())->delete((int) $params['id']);
        $this->flash('success', 'Turno eliminado.');
        $this->redirect('turnos');
    }

    /** POST /api/turnos/{id}/reagendar — drag & drop desde FullCalendar */
    public function reagendar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) { $this->json(['success'=>false,'error'=>'Token inválido.'],403); }

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = (int) $params['id'];
        $fecha= $body['fecha_inicio'] ?? '';
        $dur  = (int) ($body['duracion_min'] ?? 60);

        if (!$fecha) { $this->json(['success'=>false,'error'=>'Fecha inválida.'],422); }

        (new Turno())->reagendar($id, $fecha, $dur);
        $this->json(['success' => true]);
    }

    /** POST /turnos/{id}/estado  y  POST /api/turnos/{id}/estado */
    public function cambiarEstado(array $params = []): void
    {
        // Soporta form POST normal (desde show.php) y JSON (desde fetch)
        $isJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
               || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');

        if ($isJson) {
            if (!Auth::verifyCsrf()) { $this->json(['success'=>false,'error'=>'Token inválido.'],403); }
            $body   = json_decode(file_get_contents('php://input'), true) ?? [];
            $estado = $body['estado'] ?? '';
            $ok     = (new Turno())->cambiarEstado((int)$params['id'], $estado);
            $this->json(['success' => $ok]);
        } else {
            if (!Auth::verifyCsrf()) { $this->flash('error','Token inválido.'); $this->redirect('turnos'); }
            (new Turno())->cambiarEstado((int)$params['id'], $_POST['estado'] ?? '');
            $this->flash('success', 'Estado actualizado.');
            $this->redirect('turnos/' . $params['id']);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function toCalendarEvent(array $t): array
    {
        $colors = [
            'agendado'   => ['#3b82f6', '#1d4ed8'],
            'confirmado' => ['#22c55e', '#15803d'],
            'hecho'      => ['#6b7280', '#374151'],
            'cancelado'  => ['#ef4444', '#b91c1c'],
        ];
        [$bg, $border] = $colors[$t['estado']] ?? ['#6b7280','#374151'];

        $inicio = new \DateTime($t['fecha_inicio']);
        $fin    = (clone $inicio)->modify("+{$t['duracion_min']} minutes");

        return [
            'id'              => $t['id'],
            'title'           => $t['cliente_nombre'],
            'start'           => $inicio->format('c'),
            'end'             => $fin->format('c'),
            'backgroundColor' => $bg,
            'borderColor'     => $border,
            'extendedProps'   => [
                'estado'      => $t['estado'],
                'duracion'    => $t['duracion_min'],
                'sena'        => $t['sena'],
                'notas'       => $t['notas'],
                'cliente_id'  => $t['cliente_id'],
            ],
        ];
    }

    private function validar(array $d): array
    {
        $errors = [];
        if (empty($d['cliente_id']))   $errors[] = 'Seleccioná un cliente.';
        if (empty($d['fecha_inicio'])) $errors[] = 'La fecha y hora son obligatorias.';
        if (empty($d['duracion_min']) || (int)$d['duracion_min'] < 15)
            $errors[] = 'Duración mínima: 15 minutos.';
        return $errors;
    }
}
