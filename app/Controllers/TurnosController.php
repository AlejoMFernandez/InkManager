<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Turno;
use App\Models\Cliente;
use App\Services\WhatsAppService;

class TurnosController extends Controller
{
    /** GET /turnos — vista calendario */
    public function index(array $params = []): void
    {
        $clientes = (new Cliente())->all('nombre', 'ASC');
        $this->render('turnos.index', compact('clientes'));
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

        $model = new Turno();
        $id    = $model->crear($_POST);

        // WhatsApp: confirmación automática al agendar
        $turno = $model->conCliente($id);
        if ($turno && !empty($turno['cliente_telefono'])) {
            (new WhatsAppService())->confirmacion(
                $turno['cliente_telefono'],
                $turno['cliente_nombre'],
                $turno['fecha_inicio'],
                (int) $turno['duracion_min']
            );
        }

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

    /** POST /api/turnos — crear turno rápido desde calendario (JSON response) */
    public function crearRapido(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->json(['success' => false, 'error' => 'Token inválido.'], 403);
        }

        $errors = $this->validar($_POST);
        if ($errors) {
            $this->json(['success' => false, 'error' => implode(' ', $errors)], 422);
        }

        $model = new Turno();
        $id    = $model->crear($_POST);
        $turno = $model->conCliente($id);

        if ($turno && !empty($turno['cliente_telefono'])) {
            (new WhatsAppService())->confirmacion(
                $turno['cliente_telefono'],
                $turno['cliente_nombre'],
                $turno['fecha_inicio'],
                (int) $turno['duracion_min']
            );
        }

        $event = $turno ? $this->toCalendarEvent($turno) : null;
        $this->json(['success' => true, 'event' => $event]);
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

        $model  = new Turno();
        $turnId = (int) $params['id'];

        if ($isJson) {
            if (!Auth::verifyCsrf()) { $this->json(['success'=>false,'error'=>'Token inválido.'],403); }
            $body   = json_decode(file_get_contents('php://input'), true) ?? [];
            $estado = $body['estado'] ?? '';
            $ok     = $model->cambiarEstado($turnId, $estado);
            if ($ok) $this->notificarCambioEstado($model, $turnId, $estado);
            $this->json(['success' => $ok]);
        } else {
            if (!Auth::verifyCsrf()) { $this->flash('error','Token inválido.'); $this->redirect('turnos'); }
            $estado = $_POST['estado'] ?? '';
            $model->cambiarEstado($turnId, $estado);
            $this->notificarCambioEstado($model, $turnId, $estado);
            $this->flash('success', 'Estado actualizado.');
            $this->redirect('turnos/' . $turnId);
        }
    }

    /** POST /api/turnos/{id}/notificar — recordatorio manual desde la ficha */
    public function notificar(array $params = []): void
    {
        header('Content-Type: application/json');

        if (!Auth::verifyCsrf()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
            return;
        }

        $turno = (new Turno())->conCliente((int) $params['id']);
        if (!$turno) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Turno no encontrado.']);
            return;
        }
        if (empty($turno['cliente_telefono'])) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'El cliente no tiene número de teléfono cargado.']);
            return;
        }

        $ws = new WhatsAppService();
        if (!$ws->isEnabled()) {
            http_response_code(503);
            echo json_encode([
                'ok'    => false,
                'error' => 'WhatsApp no configurado. Agregá TWILIO_SID, TWILIO_TOKEN y TWILIO_FROM en el archivo .env.',
            ]);
            return;
        }

        $sent = $ws->recordatorio(
            $turno['cliente_telefono'],
            $turno['cliente_nombre'],
            $turno['fecha_inicio'],
            (int) $turno['duracion_min']
        );

        echo json_encode([
            'ok'    => $sent,
            'error' => $sent ? null : 'Twilio no pudo entregar el mensaje. Verificá las credenciales.',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Envía WhatsApp al cambiar a 'confirmado' o 'cancelado' (silencioso). */
    private function notificarCambioEstado(Turno $model, int $id, string $estado): void
    {
        if (!in_array($estado, ['confirmado', 'cancelado'], true)) return;
        $turno = $model->conCliente($id);
        if ($turno && !empty($turno['cliente_telefono'])) {
            (new WhatsAppService())->estadoCambiado(
                $turno['cliente_telefono'],
                $turno['cliente_nombre'],
                $estado,
                $turno['fecha_inicio']
            );
        }
    }

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
                'estado'         => $t['estado'],
                'duracion'       => $t['duracion_min'],
                'sena'           => $t['sena'],
                'notas'          => $t['notas'],
                'cliente_id'     => $t['cliente_id'],
                'cliente_nombre' => $t['cliente_nombre'],
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
