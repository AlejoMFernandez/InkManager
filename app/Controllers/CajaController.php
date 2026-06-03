<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Turno;

class CajaController extends Controller
{
    public function index(array $params = []): void
    {
        $periodo = $_GET['periodo'] ?? 'hoy';
        [$desde, $hasta] = $this->rangoFechas($periodo);

        $model = new Pago();
        $kpis  = $model->kpis();
        $pagos = $model->porPeriodo($desde, $hasta);
        $breakdown = $model->totalPorMetodo($desde, $hasta);

        $totalIng = 0.0;
        $totalEgr = 0.0;
        foreach ($pagos as $p) {
            if ($p['tipo'] === 'ingreso') $totalIng += (float) $p['monto'];
            else                          $totalEgr += (float) $p['monto'];
        }

        $csrf = Auth::csrfToken();

        $this->render('caja.index', compact(
            'pagos', 'kpis', 'breakdown', 'periodo',
            'desde', 'hasta', 'totalIng', 'totalEgr', 'csrf'
        ) + ['pageTitle' => 'Caja']);
    }

    public function nuevo(array $params = []): void
    {
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        $turnoOrigen = null;

        if (empty($old) && !empty($_GET['turno_id'])) {
            // Llegamos desde el botón "Cobrar" de un turno — pre-llenamos el form
            $turnoOrigen = (new Turno())->conCliente((int) $_GET['turno_id']);
            if ($turnoOrigen) {
                $concepto = 'Tatuaje — ' . $turnoOrigen['cliente_nombre']
                          . ' (' . date('d/m/Y', strtotime($turnoOrigen['fecha_inicio'])) . ')';
                $old = [
                    '_turno_id'  => (string) $turnoOrigen['id'],
                    'tipo'       => 'ingreso',
                    'concepto'   => $concepto,
                    'monto'      => $turnoOrigen['sena'] ? rtrim(rtrim(number_format((float)$turnoOrigen['sena'], 2, '.', ''), '0'), '.') : '',
                    'metodo'     => 'efectivo',
                    'cliente_id' => (string) $turnoOrigen['cliente_id'],
                    'notas'      => '',
                    'fecha'      => date('Y-m-d', strtotime($turnoOrigen['fecha_inicio'])),
                ];
            }
        } elseif (!empty($old['_turno_id'])) {
            // Regresamos por error de validación — restauramos el banner de turno
            $turnoOrigen = (new Turno())->conCliente((int) $old['_turno_id']);
        }

        $clientes = (new Cliente())->all('nombre');
        $csrf     = Auth::csrfToken();

        $this->render('caja.form', compact('clientes', 'old', 'csrf', 'turnoOrigen') + ['pageTitle' => 'Registrar cobro']);
    }

    public function guardar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('caja/nuevo');
        }

        $errors = $this->validar($_POST);
        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $_SESSION['old'] = $_POST;
            $this->redirect('caja/nuevo');
        }

        (new Pago())->crear($_POST);
        $this->flash('success', 'Cobro registrado correctamente.');
        $this->redirect('caja');
    }

    public function borrar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('caja');
        }

        $model = new Pago();
        $id    = (int) $params['id'];
        if (!$model->find($id)) {
            $this->flash('error', 'Registro no encontrado.');
            $this->redirect('caja');
        }

        $model->delete($id);
        $this->flash('success', 'Registro eliminado.');
        $this->redirect('caja');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function rangoFechas(string $periodo): array
    {
        return match ($periodo) {
            'semana' => [
                date('Y-m-d', strtotime('monday this week')),
                date('Y-m-d', strtotime('sunday this week')),
            ],
            'mes'  => [date('Y-m-01'), date('Y-m-t')],
            'todo' => ['2000-01-01', '2099-12-31'],
            default => [date('Y-m-d'), date('Y-m-d')],   // hoy
        };
    }

    private function validar(array $data): array
    {
        $errors = [];
        if (trim($data['concepto'] ?? '') === '') {
            $errors[] = 'El concepto es obligatorio.';
        }
        $monto = $data['monto'] ?? '';
        if (!is_numeric($monto) || (float) $monto <= 0) {
            $errors[] = 'El monto debe ser un número mayor a cero.';
        }
        if (!empty($data['fecha']) && !strtotime($data['fecha'])) {
            $errors[] = 'La fecha no es válida.';
        }
        return $errors;
    }
}
