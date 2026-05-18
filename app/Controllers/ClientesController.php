<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Cliente;

class ClientesController extends Controller
{
    private const PER_PAGE = 20;

    public function index(array $params = []): void
    {
        $term   = trim($_GET['q'] ?? '');
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $model    = new Cliente();
        $clientes = $model->buscar($term, self::PER_PAGE, $offset);
        $total    = $model->contarBusqueda($term);
        $pages    = (int) ceil($total / self::PER_PAGE);

        $this->render('clientes.index', compact('clientes', 'total', 'term', 'page', 'pages'));
    }

    public function nuevo(array $params = []): void
    {
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);
        $this->render('clientes.form', ['cliente' => null, 'old' => $old, 'pageTitle' => 'Nuevo cliente']);
    }

    public function guardar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('clientes/nuevo');
        }

        $errors = $this->validar($_POST);
        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $_SESSION['old'] = $_POST;
            $this->redirect('clientes/nuevo');
        }

        $model = new Cliente();
        $id    = $model->crear($_POST);
        $this->flash('success', 'Cliente creado correctamente.');
        $this->redirect("clientes/{$id}");
    }

    public function editar(array $params = []): void
    {
        $model   = new Cliente();
        $cliente = $model->find((int) $params['id']);
        if (!$cliente) { $this->notFound(); return; }

        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);
        $this->render('clientes.form', compact('cliente', 'old') + ['pageTitle' => 'Editar cliente']);
    }

    public function actualizar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect("clientes/{$params['id']}/editar");
        }

        $id    = (int) $params['id'];
        $model = new Cliente();
        if (!$model->find($id)) { $this->notFound(); return; }

        $errors = $this->validar($_POST);
        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $_SESSION['old'] = $_POST;
            $this->redirect("clientes/{$id}/editar");
        }

        $model->actualizar($id, $_POST);
        $this->flash('success', 'Cliente actualizado.');
        $this->redirect("clientes/{$id}");
    }

    public function borrar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('clientes');
        }

        $id    = (int) $params['id'];
        $model = new Cliente();
        if (!$model->find($id)) { $this->notFound(); return; }

        $model->delete($id);
        $this->flash('success', 'Cliente eliminado.');
        $this->redirect('clientes');
    }

    public function ficha(array $params = []): void
    {
        $model   = new Cliente();
        $cliente = $model->conTatuajes((int) $params['id']);
        if (!$cliente) { $this->notFound(); return; }

        // Próximos turnos del cliente
        $stmt = Database::get()->prepare(
            "SELECT * FROM turnos
             WHERE cliente_id = ? AND fecha_inicio >= NOW()
               AND estado IN ('agendado','confirmado')
             ORDER BY fecha_inicio ASC LIMIT 5"
        );
        $stmt->execute([$cliente['id']]);
        $turnos = $stmt->fetchAll();

        $estilos = (new \App\Models\Tatuaje())->estilos();
        $this->render('clientes.show', compact('cliente', 'turnos', 'estilos'));
    }

    private function validar(array $data): array
    {
        $errors = [];
        $nombre = trim($data['nombre'] ?? '');
        if ($nombre === '') {
            $errors[] = 'El nombre es obligatorio.';
        } elseif (mb_strlen($nombre) > 120) {
            $errors[] = 'El nombre no puede superar 120 caracteres.';
        }
        if (isset($data['instagram']) && mb_strlen($data['instagram']) > 80) {
            $errors[] = 'El Instagram no puede superar 80 caracteres.';
        }
        if (isset($data['telefono']) && mb_strlen($data['telefono']) > 30) {
            $errors[] = 'El teléfono no puede superar 30 caracteres.';
        }
        if (!empty($data['primera_visita']) && !strtotime($data['primera_visita'])) {
            $errors[] = 'La fecha de primera visita no es válida.';
        }
        return $errors;
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->render('clientes.index', [
            'clientes' => [], 'total' => 0, 'term' => '', 'page' => 1, 'pages' => 0,
        ]);
    }
}
