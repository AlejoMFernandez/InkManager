<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Galeria;
use App\Models\Cliente;

class GaleriaController extends Controller
{
    // ── GET /galeria ──────────────────────────────────────────────────────────

    public function index(array $params = []): void
    {
        $estilo = $_GET['estilo'] ?? '';
        $fotos  = (new Galeria())->todos($estilo);
        $csrf   = Auth::csrfToken();

        $this->render('galeria.index', compact('fotos', 'estilo', 'csrf') + ['pageTitle' => 'Galería']);
    }

    // ── GET /galeria/subir ────────────────────────────────────────────────────

    public function subir(array $params = []): void
    {
        $old      = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        $clientes = (new Cliente())->all('nombre');
        $csrf     = Auth::csrfToken();

        $this->render('galeria.form', compact('clientes', 'old', 'csrf') + ['pageTitle' => 'Subir foto']);
    }

    // ── POST /galeria/subir ───────────────────────────────────────────────────

    public function guardar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('galeria/subir');
        }

        $file = $_FILES['foto'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Seleccioná una imagen para subir.');
            $_SESSION['old'] = $_POST;
            $this->redirect('galeria/subir');
        }

        // Validar MIME real (no confiar en extensión)
        $finfo   = new \finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png',
                    'image/webp' => 'webp', 'image/gif' => 'gif'];

        if (!isset($allowed[$mime])) {
            $this->flash('error', 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.');
            $_SESSION['old'] = $_POST;
            $this->redirect('galeria/subir');
        }
        if ($file['size'] > 8 * 1024 * 1024) {
            $this->flash('error', 'La imagen no puede superar 8 MB.');
            $_SESSION['old'] = $_POST;
            $this->redirect('galeria/subir');
        }

        // Directorio destino
        $dir = PUBLIC_PATH . '/assets/uploads/gallery/';
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $this->flash('error', 'No se pudo crear el directorio de subida.');
            $this->redirect('galeria/subir');
        }

        $filename = uniqid('g_', true) . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            $this->flash('error', 'No se pudo guardar la imagen. Verificá permisos de la carpeta.');
            $_SESSION['old'] = $_POST;
            $this->redirect('galeria/subir');
        }

        (new Galeria())->crear(array_merge($_POST, ['archivo' => $filename]));
        $this->flash('success', 'Foto agregada a la galería.');
        $this->redirect('galeria');
    }

    // ── POST /galeria/{id}/borrar ─────────────────────────────────────────────

    public function borrar(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('galeria');
        }

        $model = new Galeria();
        $id    = (int) $params['id'];
        $foto  = $model->find($id);

        if (!$foto) {
            $this->flash('error', 'Foto no encontrada.');
            $this->redirect('galeria');
        }

        // Eliminar archivo físico
        $filepath = PUBLIC_PATH . '/assets/uploads/gallery/' . basename((string) $foto['archivo']);
        if (is_file($filepath)) {
            @unlink($filepath);
        }

        $model->borrar($id);
        $this->flash('success', 'Foto eliminada.');
        $this->redirect('galeria');
    }
}
