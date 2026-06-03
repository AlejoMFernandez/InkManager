<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Tatuaje;
use App\Models\Cliente;

class TatuajesController extends Controller
{
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_BYTES    = 5 * 1024 * 1024; // 5 MB

    /** POST /api/tatuajes — crear */
    public function store(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->json(['success' => false, 'error' => 'Token inválido.'], 403);
        }

        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        if (!$clienteId || !(new Cliente())->find($clienteId)) {
            $this->json(['success' => false, 'error' => 'Cliente no encontrado.'], 404);
        }

        try {
            $fotoPath = $this->handleUpload();
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        $data = [
            'cliente_id'       => $clienteId,
            'pos_x'            => $_POST['pos_x']    ?? null,
            'pos_y'            => $_POST['pos_y']    ?? null,
            'pos_z'            => $_POST['pos_z']    ?? null,
            'normal_x'         => $_POST['normal_x'] ?? null,
            'normal_y'         => $_POST['normal_y'] ?? null,
            'normal_z'         => $_POST['normal_z'] ?? null,
            'foto_path'        => $fotoPath,
            'estilo_id'        => $_POST['estilo_id']        ?? null,
            'fecha'            => $_POST['fecha']            ?? null,
            'precio'           => $_POST['precio']           ?? null,
            'sesiones_totales' => $_POST['sesiones_totales'] ?? 1,
            'sesiones_hechas'  => $_POST['sesiones_hechas']  ?? 0,
            'notas'            => $_POST['notas']            ?? null,
            'tamano'           => $_POST['tamano']           ?? 'm',
            'zona'             => $_POST['zona']             ?? null,
            'tinta'            => $_POST['tinta']            ?? 'negro',
        ];

        $model = new Tatuaje();
        $id    = $model->crear($data);
        $tat   = $this->enriched($model->find($id));

        $this->json(['success' => true, 'tatuaje' => $tat]);
    }

    /** POST /api/tatuajes/{id}/actualizar — editar */
    public function update(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->json(['success' => false, 'error' => 'Token inválido.'], 403);
        }

        $model  = new Tatuaje();
        $id     = (int) $params['id'];
        $actual = $model->find($id);
        if (!$actual) {
            $this->json(['success' => false, 'error' => 'Tatuaje no encontrado.'], 404);
        }

        try {
            $fotoPath = $this->handleUpload();
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        $data = [
            'estilo_id'        => $_POST['estilo_id']        ?? null,
            'fecha'            => $_POST['fecha']            ?? null,
            'precio'           => $_POST['precio']           ?? null,
            'sesiones_totales' => $_POST['sesiones_totales'] ?? 1,
            'sesiones_hechas'  => $_POST['sesiones_hechas']  ?? 0,
            'notas'            => $_POST['notas']            ?? null,
            'tamano'           => $_POST['tamano']           ?? 'm',
            'zona'             => $_POST['zona']             ?? null,
            'tinta'            => $_POST['tinta']            ?? 'negro',
        ];

        if ($fotoPath !== null) {
            // Borrar foto anterior
            if ($actual['foto_path']) {
                $old = PUBLIC_PATH . '/assets/uploads/' . $actual['foto_path'];
                if (file_exists($old)) @unlink($old);
            }
            $data['foto_path'] = $fotoPath;
        }

        $model->actualizar($id, $data);
        $this->json(['success' => true, 'tatuaje' => $this->enriched($model->find($id))]);
    }

    /** POST /api/tatuajes/{id}/foto — subir o reemplazar sólo la foto */
    public function uploadFoto(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->json(['success' => false, 'error' => 'Token inválido.'], 403);
        }

        $model  = new Tatuaje();
        $id     = (int) $params['id'];
        $actual = $model->find($id);
        if (!$actual) {
            $this->json(['success' => false, 'error' => 'Tatuaje no encontrado.'], 404);
        }

        try {
            $fotoPath = $this->handleUpload();
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        if ($fotoPath === null) {
            $this->json(['success' => false, 'error' => 'No se recibió ninguna foto.'], 422);
        }

        // Borrar foto anterior si existe
        if ($actual['foto_path']) {
            $old = PUBLIC_PATH . '/assets/uploads/' . $actual['foto_path'];
            if (file_exists($old)) @unlink($old);
        }

        $model->updateFoto($id, $fotoPath);
        $this->json(['success' => true, 'tatuaje' => $this->enriched($model->find($id))]);
    }

    /** POST /api/tatuajes/{id}/borrar — eliminar */
    public function destroy(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->json(['success' => false, 'error' => 'Token inválido.'], 403);
        }

        $model  = new Tatuaje();
        $id     = (int) $params['id'];
        $actual = $model->find($id);
        if (!$actual) {
            $this->json(['success' => false, 'error' => 'Tatuaje no encontrado.'], 404);
        }

        if ($actual['foto_path']) {
            $path = PUBLIC_PATH . '/assets/uploads/' . $actual['foto_path'];
            if (file_exists($path)) @unlink($path);
        }

        $model->delete($id);
        $this->json(['success' => true]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function handleUpload(): ?string
    {
        $file = $_FILES['foto'] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Error al subir el archivo (código ' . $file['error'] . ').');
        }
        if ($file['size'] > self::MAX_BYTES) {
            throw new \InvalidArgumentException('La foto no puede superar 5 MB.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new \InvalidArgumentException('Formato no permitido. Usá JPG, PNG o WebP.');
        }

        $ext      = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest     = PUBLIC_PATH . '/assets/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('No se pudo guardar la foto.');
        }

        return $filename;
    }

    /** Agrega campos derivados al array de tatuaje para el JSON de respuesta */
    private function enriched(array $t): array
    {
        // Resolver nombre de estilo
        if (!empty($t['estilo_id'])) {
            $stmt = Database::get()->prepare('SELECT nombre FROM estilos WHERE id = ?');
            $stmt->execute([(int) $t['estilo_id']]);
            $row = $stmt->fetch();
            $t['estilo_nombre'] = $row['nombre'] ?? null;
        } else {
            $t['estilo_nombre'] = null;
        }

        $t['foto_url'] = $t['foto_path']
            ? PUBLIC_URL . '/assets/uploads/' . $t['foto_path']
            : null;

        // Castear floats y enteros
        foreach (['pos_x','pos_y','pos_z','normal_x','normal_y','normal_z','precio'] as $f) {
            if ($t[$f] !== null) $t[$f] = (float) $t[$f];
        }
        foreach (['id','cliente_id','estilo_id','sesiones_totales','sesiones_hechas'] as $f) {
            if (isset($t[$f])) $t[$f] = (int) $t[$f];
        }

        return $t;
    }
}
