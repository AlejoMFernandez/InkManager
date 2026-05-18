<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Turno extends Model
{
    protected string $table = 'turnos';

    /** Todos los turnos en rango para FullCalendar (JSON events) */
    public function enRango(string $desde, string $hasta): array
    {
        return $this->query(
            "SELECT tu.*, c.nombre AS cliente_nombre
             FROM turnos tu
             JOIN clientes c ON c.id = tu.cliente_id
             WHERE tu.fecha_inicio BETWEEN ? AND ?
             ORDER BY tu.fecha_inicio",
            [$desde, $hasta]
        );
    }

    /** Próximos N turnos (para dashboard) */
    public function proximos(int $limit = 10): array
    {
        return $this->query(
            "SELECT tu.*, c.nombre AS cliente_nombre
             FROM turnos tu
             JOIN clientes c ON c.id = tu.cliente_id
             WHERE tu.fecha_inicio >= NOW()
               AND tu.estado IN ('agendado','confirmado')
             ORDER BY tu.fecha_inicio ASC
             LIMIT ?",
            [$limit]
        );
    }

    public function crear(array $d): int
    {
        $this->execute(
            'INSERT INTO turnos (cliente_id, tatuaje_id, fecha_inicio, duracion_min, estado, sena, notas)
             VALUES (?,?,?,?,?,?,?)',
            [
                (int)   $d['cliente_id'],
                !empty($d['tatuaje_id']) ? (int) $d['tatuaje_id'] : null,
                $d['fecha_inicio'],
                (int) ($d['duracion_min'] ?? 60),
                $d['estado']    ?? 'agendado',
                !empty($d['sena'])  ? (float) $d['sena'] : null,
                $d['notas']     ?? null,
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool
    {
        return $this->execute(
            'UPDATE turnos SET cliente_id=?,tatuaje_id=?,fecha_inicio=?,
             duracion_min=?,estado=?,sena=?,notas=? WHERE id=?',
            [
                (int)   $d['cliente_id'],
                !empty($d['tatuaje_id']) ? (int) $d['tatuaje_id'] : null,
                $d['fecha_inicio'],
                (int) ($d['duracion_min'] ?? 60),
                $d['estado']    ?? 'agendado',
                !empty($d['sena'])  ? (float) $d['sena'] : null,
                $d['notas']     ?? null,
                $id,
            ]
        );
    }

    public function reagendar(int $id, string $fechaInicio, int $duracionMin): bool
    {
        return $this->execute(
            'UPDATE turnos SET fecha_inicio=?, duracion_min=? WHERE id=?',
            [$fechaInicio, $duracionMin, $id]
        );
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $validos = ['agendado', 'confirmado', 'hecho', 'cancelado'];
        if (!in_array($estado, $validos, true)) return false;
        return $this->execute('UPDATE turnos SET estado=? WHERE id=?', [$estado, $id]);
    }

    public function conCliente(int $id): ?array
    {
        return $this->queryOne(
            'SELECT tu.*, c.nombre AS cliente_nombre, c.instagram AS cliente_instagram
             FROM turnos tu JOIN clientes c ON c.id = tu.cliente_id
             WHERE tu.id = ?',
            [$id]
        );
    }
}
