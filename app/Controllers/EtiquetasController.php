<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Etiqueta;

/**
 * Endpoints JSON para gestión de etiquetas.
 * Todas las respuestas son { ok: bool, ... }
 */
class EtiquetasController extends Controller
{
    /** GET /api/etiquetas — lista completa */
    public function index(array $params = []): void
    {
        header('Content-Type: application/json');
        $model = new Etiqueta();
        echo json_encode(['ok' => true, 'etiquetas' => $model->todas()]);
    }

    /** POST /api/etiquetas — crear nueva etiqueta */
    public function crear(array $params = []): void
    {
        header('Content-Type: application/json');

        if (!Auth::verifyCsrf()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
            return;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $color  = trim($_POST['color']  ?? '#ef4444');

        if ($nombre === '' || mb_strlen($nombre) > 40) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Nombre inválido (1–40 caracteres).']);
            return;
        }

        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = '#ef4444';
        }

        $model = new Etiqueta();
        $id    = $model->crear($nombre, $color);
        $tag   = $model->find($id);

        echo json_encode(['ok' => true, 'etiqueta' => $tag]);
    }

    /** POST /api/etiquetas/{id}/editar — actualizar nombre/color */
    public function editar(array $params = []): void
    {
        header('Content-Type: application/json');

        if (!Auth::verifyCsrf()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
            return;
        }

        $id     = (int) ($params['id'] ?? 0);
        $model  = new Etiqueta();
        $tag    = $model->find($id);

        if (!$tag) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Etiqueta no encontrada.']);
            return;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $color  = trim($_POST['color']  ?? $tag['color']);

        if ($nombre === '' || mb_strlen($nombre) > 40) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Nombre inválido (1–40 caracteres).']);
            return;
        }

        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = $tag['color'];
        }

        $model->actualizar($id, $nombre, $color);
        echo json_encode(['ok' => true, 'etiqueta' => $model->find($id)]);
    }

    /** POST /api/etiquetas/{id}/borrar */
    public function borrar(array $params = []): void
    {
        header('Content-Type: application/json');

        if (!Auth::verifyCsrf()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
            return;
        }

        $id    = (int) ($params['id'] ?? 0);
        $model = new Etiqueta();

        if (!$model->find($id)) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Etiqueta no encontrada.']);
            return;
        }

        $model->delete($id);
        echo json_encode(['ok' => true]);
    }

    /** POST /api/clientes/{cliente_id}/etiquetas/{id}/asignar */
    public function asignar(array $params = []): void
    {
        header('Content-Type: application/json');

        if (!Auth::verifyCsrf()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
            return;
        }

        $clienteId   = (int) ($params['cliente_id'] ?? 0);
        $etiquetaId  = (int) ($params['id'] ?? 0);
        $model       = new Etiqueta();

        if (!$model->find($etiquetaId)) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Etiqueta no encontrada.']);
            return;
        }

        $model->asignar($clienteId, $etiquetaId);
        echo json_encode(['ok' => true]);
    }

    /** POST /api/clientes/{cliente_id}/etiquetas/{id}/quitar */
    public function quitar(array $params = []): void
    {
        header('Content-Type: application/json');

        if (!Auth::verifyCsrf()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
            return;
        }

        $clienteId   = (int) ($params['cliente_id'] ?? 0);
        $etiquetaId  = (int) ($params['id'] ?? 0);
        $model       = new Etiqueta();

        $model->quitar($clienteId, $etiquetaId);
        echo json_encode(['ok' => true]);
    }
}
