<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Etiqueta extends Model
{
    protected string $table  = 'etiquetas';
    protected bool   $scoped = true;

    public const PRESET_COLORS = [
        '#ef4444', '#f97316', '#eab308', '#22c55e',
        '#3b82f6', '#a855f7', '#ec4899', '#6b7280',
    ];

    /** Todas las etiquetas del studio, ordenadas por nombre */
    public function todas(): array
    {
        $sid = $this->sid();
        if ($sid > 0) {
            return $this->query(
                'SELECT * FROM etiquetas WHERE studio_id = ? ORDER BY nombre ASC',
                [$sid]
            );
        }
        return $this->query('SELECT * FROM etiquetas ORDER BY nombre ASC');
    }

    /** Etiquetas de un cliente (dentro del studio actual) */
    public function deCliente(int $clienteId): array
    {
        $sid    = $this->sid();
        $extra  = '';
        $params = [$clienteId];
        if ($sid > 0) { $extra = 'AND e.studio_id = ?'; $params[] = $sid; }

        return $this->query(
            "SELECT e.*
             FROM etiquetas e
             JOIN cliente_etiquetas ce ON ce.etiqueta_id = e.id
             WHERE ce.cliente_id = ? {$extra}
             ORDER BY e.nombre ASC",
            $params
        );
    }

    /** Asignar etiqueta a cliente (ignora duplicado) */
    public function asignar(int $clienteId, int $etiquetaId): bool
    {
        return $this->execute(
            'INSERT IGNORE INTO cliente_etiquetas (cliente_id, etiqueta_id) VALUES (?, ?)',
            [$clienteId, $etiquetaId]
        );
    }

    /** Quitar etiqueta de cliente */
    public function quitar(int $clienteId, int $etiquetaId): bool
    {
        return $this->execute(
            'DELETE FROM cliente_etiquetas WHERE cliente_id = ? AND etiqueta_id = ?',
            [$clienteId, $etiquetaId]
        );
    }

    /** Crear nueva etiqueta (scoped al studio actual) */
    public function crear(string $nombre, string $color): int
    {
        $sid = $this->sid();
        $this->execute(
            'INSERT INTO etiquetas (studio_id, nombre, color) VALUES (?,?,?)',
            [$sid ?: 1, trim($nombre), $color]
        );
        return $this->lastInsertId();
    }

    /** Actualizar etiqueta (solo si pertenece al studio actual) */
    public function actualizar(int $id, string $nombre, string $color): bool
    {
        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND studio_id = ?' : '';
        $params    = [trim($nombre), $color, $id];
        if ($sid > 0) $params[] = $sid;

        return $this->execute(
            "UPDATE etiquetas SET nombre = ?, color = ? WHERE id = ? {$andStudio}",
            $params
        );
    }
}
