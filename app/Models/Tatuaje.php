<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Tatuaje extends Model
{
    protected string $table  = 'tatuajes';
    protected bool   $scoped = true;

    public function porCliente(int $clienteId): array
    {
        $sid    = $this->sid();
        $extra  = '';
        $params = [$clienteId];
        if ($sid > 0) { $extra = 'AND t.studio_id = ?'; $params[] = $sid; }

        return $this->query(
            "SELECT t.*, e.nombre AS estilo_nombre
             FROM tatuajes t
             LEFT JOIN estilos e ON e.id = t.estilo_id
             WHERE t.cliente_id = ? {$extra}
             ORDER BY t.fecha DESC",
            $params
        );
    }

    public function crear(array $d): int
    {
        $sid = $this->sid();
        $this->execute(
            'INSERT INTO tatuajes
                (studio_id, cliente_id, pos_x, pos_y, pos_z, normal_x, normal_y, normal_z,
                 foto_path, estilo_id, fecha, precio, sesiones_totales, sesiones_hechas,
                 notas, tamano, zona, tinta)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $sid ?: 1,
                (int)   $d['cliente_id'],
                isset($d['pos_x'])    ? (float) $d['pos_x']    : null,
                isset($d['pos_y'])    ? (float) $d['pos_y']    : null,
                isset($d['pos_z'])    ? (float) $d['pos_z']    : null,
                isset($d['normal_x']) ? (float) $d['normal_x'] : null,
                isset($d['normal_y']) ? (float) $d['normal_y'] : null,
                isset($d['normal_z']) ? (float) $d['normal_z'] : null,
                $d['foto_path']       ?? null,
                !empty($d['estilo_id']) ? (int)   $d['estilo_id'] : null,
                !empty($d['fecha'])     ? $d['fecha']             : null,
                !empty($d['precio'])    ? (float)  $d['precio']   : null,
                (int) ($d['sesiones_totales'] ?? 1),
                (int) ($d['sesiones_hechas']  ?? 0),
                $d['notas']  ?? null,
                in_array($d['tamano'] ?? '', ['xs','s','m','l','xl'], true) ? $d['tamano'] : 'm',
                !empty($d['zona'])  ? $d['zona']  : null,
                in_array($d['tinta'] ?? '', ['negro','gris','color'], true) ? $d['tinta']  : 'negro',
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool
    {
        $sid  = $this->sid();
        $sets = ['estilo_id=?','fecha=?','precio=?','sesiones_totales=?','sesiones_hechas=?',
                 'notas=?','tamano=?','zona=?','tinta=?'];
        $params = [
            !empty($d['estilo_id']) ? (int)   $d['estilo_id'] : null,
            !empty($d['fecha'])     ? $d['fecha']             : null,
            !empty($d['precio'])    ? (float)  $d['precio']   : null,
            (int) ($d['sesiones_totales'] ?? 1),
            (int) ($d['sesiones_hechas']  ?? 0),
            $d['notas'] ?? null,
            in_array($d['tamano'] ?? '', ['xs','s','m','l','xl'], true) ? $d['tamano'] : 'm',
            !empty($d['zona'])  ? $d['zona']  : null,
            in_array($d['tinta'] ?? '', ['negro','gris','color'], true) ? $d['tinta']  : 'negro',
        ];

        if (isset($d['foto_path'])) {
            $sets[]   = 'foto_path=?';
            $params[] = $d['foto_path'];
        }

        $andStudio = $sid > 0 ? 'AND studio_id = ?' : '';
        $params[]  = $id;
        if ($sid > 0) $params[] = $sid;

        return $this->execute(
            'UPDATE tatuajes SET ' . implode(',', $sets) . " WHERE id=? {$andStudio}",
            $params
        );
    }

    /** Actualiza sólo la foto (sin tocar los demás campos) */
    public function updateFoto(int $id, string $path): bool
    {
        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND studio_id = ?' : '';
        $params    = [$path, $id];
        if ($sid > 0) $params[] = $sid;

        return $this->execute(
            "UPDATE tatuajes SET foto_path = ? WHERE id = ? {$andStudio}",
            $params
        );
    }

    public function actualizarPosicion(int $id, float $x, float $y, float $z,
                                       float $nx, float $ny, float $nz): bool
    {
        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND studio_id = ?' : '';
        $params    = [$x, $y, $z, $nx, $ny, $nz, $id];
        if ($sid > 0) $params[] = $sid;

        return $this->execute(
            "UPDATE tatuajes SET pos_x=?,pos_y=?,pos_z=?,normal_x=?,normal_y=?,normal_z=?
             WHERE id=? {$andStudio}",
            $params
        );
    }

    /** Todos los estilos para los selects — global (no scoped) */
    public function estilos(): array
    {
        return $this->query('SELECT * FROM estilos ORDER BY nombre');
    }
}
