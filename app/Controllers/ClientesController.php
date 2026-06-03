<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Cliente;
use App\Models\Etiqueta;
use App\Models\Pago;
use App\Models\Presupuesto;
use App\Models\Studio;

class ClientesController extends Controller
{
    private const PER_PAGE = 20;

    public function index(array $params = []): void
    {
        $term       = trim($_GET['q'] ?? '');
        $etiquetaId = max(0, (int) ($_GET['etiqueta'] ?? 0));
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $offset     = ($page - 1) * self::PER_PAGE;

        $model    = new Cliente();
        $clientes = $model->buscar($term, self::PER_PAGE, $offset, $etiquetaId);
        $total    = $model->contarBusqueda($term, $etiquetaId);
        $pages    = (int) ceil($total / self::PER_PAGE);

        $etiquetas    = (new Etiqueta())->todas();
        $etiquetaActiva = $etiquetaId > 0 ? (new Etiqueta())->find($etiquetaId) : null;

        $this->render('clientes.index', compact(
            'clientes', 'total', 'term', 'page', 'pages',
            'etiquetas', 'etiquetaId', 'etiquetaActiva'
        ));
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

        // ── Plan limit check ─────────────────────────────────────────────
        $sid = Auth::studioId();
        if ($sid > 0) {
            $studioModel = new Studio();
            if ($studioModel->planReached($sid, 'clientes')) {
                $limits = $studioModel->planLimits($sid);
                $this->flash('error',
                    'Límite del plan Free alcanzado (' . $limits['max_clientes'] . ' clientes). '
                    . 'Actualizá a Pro para agregar más.'
                );
                $this->redirect('clientes');
            }
        }

        $model = new Cliente();
        $id    = $model->crear($_POST);

        // Avatar opcional subido junto con el formulario de creación
        if (!empty($_FILES['avatar']['tmp_name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $this->handleAvatarUpload((int) $id, $model);
        }

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

        // Próximos turnos del cliente (scoped)
        $sid  = Auth::studioId();
        $sidW = $sid > 0 ? 'AND tu.studio_id = ?' : '';
        $db   = Database::get();

        $stmtT = $db->prepare(
            "SELECT * FROM turnos tu
             WHERE tu.cliente_id = ? AND tu.fecha_inicio >= NOW()
               AND tu.estado IN ('agendado','confirmado')
               {$sidW}
             ORDER BY tu.fecha_inicio ASC LIMIT 5"
        );
        $turnParams = [$cliente['id']];
        if ($sid > 0) $turnParams[] = $sid;
        $stmtT->execute($turnParams);
        $turnos = $stmtT->fetchAll();

        // Timeline (scoped)
        $stmtH = $db->prepare(
            "SELECT tu.id, tu.fecha_inicio, tu.duracion_min, tu.estado, tu.sena, tu.notas,
                    t.id AS tat_id, t.foto_path AS tat_foto, t.precio AS tat_precio,
                    t.sesiones_totales, t.sesiones_hechas,
                    e.nombre AS estilo_nombre
             FROM turnos tu
             LEFT JOIN tatuajes t  ON t.id = tu.tatuaje_id
             LEFT JOIN estilos  e  ON e.id = t.estilo_id
             WHERE tu.cliente_id = ? {$sidW}
             ORDER BY tu.fecha_inicio DESC"
        );
        $histParams = [$cliente['id']];
        if ($sid > 0) $histParams[] = $sid;
        $stmtH->execute($histParams);
        $historial = $stmtH->fetchAll();

        $estilos      = (new \App\Models\Tatuaje())->estilos();
        $clienteTags  = (new Etiqueta())->deCliente((int) $cliente['id']);
        $etiquetas    = (new Etiqueta())->todas();
        $presupuestos = (new Presupuesto())->porCliente((int) $cliente['id']);
        $pagos        = (new Pago())->porCliente((int) $cliente['id']);

        $this->render('clientes.show', compact(
            'cliente', 'turnos', 'estilos', 'historial',
            'clienteTags', 'etiquetas', 'presupuestos', 'pagos'
        ));
    }

    public function uploadAvatar(array $params = []): void
    {
        header('Content-Type: application/json');

        if (!Auth::verifyCsrf()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
            return;
        }

        $id    = (int) ($params['id'] ?? 0);
        $model = new Cliente();
        $cliente = $model->find($id);
        if (!$cliente) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Cliente no encontrado.']);
            return;
        }

        $file = $_FILES['avatar'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            $errMsg = ($file && $file['error'] !== UPLOAD_ERR_NO_FILE)
                ? 'Error al subir el archivo (código ' . $file['error'] . ').'
                : 'No se recibió ningún archivo.';
            echo json_encode(['ok' => false, 'error' => $errMsg]);
            return;
        }

        if ($file['size'] > 3 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'La imagen no puede superar 3 MB.']);
            return;
        }

        $mime    = mime_content_type($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Solo se aceptan imágenes JPG, PNG o WebP.']);
            return;
        }

        $ext      = $allowed[$mime];
        $filename = "avatar_{$id}_" . time() . ".{$ext}";
        $savePath = PUBLIC_PATH . '/assets/uploads/avatars/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $savePath)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el archivo en el servidor.']);
            return;
        }

        // Delete old avatar file
        if (!empty($cliente['foto_perfil'])) {
            $oldFile = PUBLIC_PATH . '/assets/uploads/avatars/' . basename((string) $cliente['foto_perfil']);
            if (is_file($oldFile)) @unlink($oldFile);
        }

        $model->actualizarAvatar($id, $filename);

        echo json_encode([
            'ok'  => true,
            'url' => PUBLIC_URL . '/assets/uploads/avatars/' . $filename,
        ]);
    }

    /**
     * Procesa y guarda el avatar subido por el form de creación (sin respuesta JSON).
     * Errores silenciosos — la foto es opcional, no debe interrumpir el flujo.
     */
    private function handleAvatarUpload(int $id, Cliente $model): void
    {
        $file = $_FILES['avatar'];
        if ($file['size'] > 3 * 1024 * 1024) return;

        $mime    = mime_content_type($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) return;

        $ext      = $allowed[$mime];
        $filename = "avatar_{$id}_" . time() . ".{$ext}";
        $savePath = PUBLIC_PATH . '/assets/uploads/avatars/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $savePath)) {
            $model->actualizarAvatar($id, $filename);
        }
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
