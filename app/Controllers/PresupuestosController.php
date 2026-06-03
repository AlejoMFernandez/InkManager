<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Presupuesto;
use App\Models\Cliente;

class PresupuestosController extends Controller
{
    /** GET /presupuestos */
    public function index(array $params = []): void
    {
        $estado      = $_GET['estado'] ?? null;
        $estadoValido = in_array($estado, ['borrador','enviado','aceptado','rechazado'], true)
                       ? $estado : null;

        $presupuestos = (new Presupuesto())->todos($estadoValido);
        $this->render('presupuestos.index', compact('presupuestos', 'estadoValido'));
    }

    /** GET /presupuestos/nuevo  |  GET /presupuestos/nuevo?cliente_id=N */
    public function nuevo(array $params = []): void
    {
        $clientes     = (new Cliente())->all('nombre', 'ASC');
        $clienteIdPre = (int) ($_GET['cliente_id'] ?? 0);
        $old          = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        $this->render('presupuestos.form', [
            'presupuesto'  => null,
            'clientes'     => $clientes,
            'clienteIdPre' => $clienteIdPre,
            'old'          => $old,
            'pageTitle'    => 'Nuevo presupuesto',
        ]);
    }

    /** POST /presupuestos/nuevo */
    public function guardar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('presupuestos/nuevo');
        }

        $errors = $this->validar($_POST);
        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $_SESSION['old'] = $_POST;
            $this->redirect('presupuestos/nuevo');
        }

        $id = (new Presupuesto())->crear($_POST);
        $this->flash('success', 'Presupuesto creado correctamente.');
        $this->redirect("presupuestos/{$id}");
    }

    /** GET /presupuestos/{id} */
    public function ver(array $params = []): void
    {
        $presupuesto = (new Presupuesto())->conCliente((int) $params['id']);
        if (!$presupuesto) {
            $this->redirect('presupuestos');
            return;
        }
        $this->render('presupuestos.show', compact('presupuesto'));
    }

    /** GET /presupuestos/{id}/editar */
    public function editar(array $params = []): void
    {
        $presupuesto = (new Presupuesto())->find((int) $params['id']);
        if (!$presupuesto) {
            $this->redirect('presupuestos');
            return;
        }
        $clientes = (new Cliente())->all('nombre', 'ASC');
        $old      = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        $this->render('presupuestos.form', [
            'presupuesto'  => $presupuesto,
            'clientes'     => $clientes,
            'clienteIdPre' => 0,
            'old'          => $old,
            'pageTitle'    => 'Editar presupuesto',
        ]);
    }

    /** POST /presupuestos/{id}/editar */
    public function actualizar(array $params = []): void
    {
        $id = (int) $params['id'];
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect("presupuestos/{$id}/editar");
        }

        $errors = $this->validar($_POST);
        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $_SESSION['old'] = $_POST;
            $this->redirect("presupuestos/{$id}/editar");
        }

        (new Presupuesto())->actualizar($id, $_POST);
        $this->flash('success', 'Presupuesto actualizado.');
        $this->redirect("presupuestos/{$id}");
    }

    /** POST /presupuestos/{id}/borrar */
    public function borrar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('presupuestos');
        }
        (new Presupuesto())->delete((int) $params['id']);
        $this->flash('success', 'Presupuesto eliminado.');
        $this->redirect('presupuestos');
    }

    /** POST /presupuestos/{id}/estado */
    public function cambiarEstado(array $params = []): void
    {
        $id = (int) $params['id'];
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect("presupuestos/{$id}");
        }

        $estado = $_POST['estado'] ?? '';
        (new Presupuesto())->cambiarEstado($id, $estado);
        $this->flash('success', 'Estado actualizado.');
        $this->redirect("presupuestos/{$id}");
    }

    /** GET /presupuestos/{id}/imprimir — standalone print view */
    public function imprimir(array $params = []): void
    {
        $presupuesto = (new Presupuesto())->conCliente((int) $params['id']);
        if (!$presupuesto) {
            $this->redirect('presupuestos');
            return;
        }

        // Load studio info for the header
        $studio = (new \App\Models\Studio())->find(\App\Core\Auth::studioId());

        $this->render('presupuestos.print', compact('presupuesto', 'studio'), null);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function validar(array $d): array
    {
        $errors = [];
        if (empty($d['cliente_id']))  $errors[] = 'Seleccioná un cliente.';
        if (empty($d['titulo']))      $errors[] = 'El título es obligatorio.';
        if (empty($d['fecha']))       $errors[] = 'La fecha es obligatoria.';
        if (!empty($d['monto']) && !is_numeric($d['monto']))
            $errors[] = 'El monto debe ser un número válido.';
        return $errors;
    }
}
