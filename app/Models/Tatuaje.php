<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Tatuaje extends Model
{
    protected string $table = 'tatuajes';

    public function porCliente(int $clienteId): array
    {
        return $this->query(
            'SELECT t.*, e.nombre AS estilo_nombre
             FROM tatuajes t
             LEFT JOIN estilos e ON e.id = t.estilo_id
             WHERE t.cliente_id = ?
             ORDER BY t.fecha DESC',
            [$clienteId]
        );
    }

    public function crear(array $d): int
    {
        $this->execute(
            'INSERT INTO tatuajes
                (cliente_id, pos_x, pos_y, pos_z, normal_x, normal_y, normal_z,
                 foto_path, estilo_id, fecha, precio, sesiones_totales, sesiones_hechas, notas)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                (int)   $d['cliente_id'],
                isset($d['pos_x'])    ? (float) $d['pos_x']    : null,
                isset($d['pos_y'])    ? (float) $d['pos_y']    : null,
                isset($d['pos_z'])    ? (float) $d['pos_z']    : null,
                isset($d['normal_x']) ? (float) $d['normal_x'] : null,
                isset($d['normal_y']) ? (float) $d['normal_y'] : null,
                isset($d['normal_z']) ? (float) $d['normal_z'] : null,
                $d['foto_path']       ?? null,
                !empty($d['estilo_id']) ? (int) $d['estilo_id'] : null,
                !empty($d['fecha'])     ? $d['fecha']           : null,
                !empty($d['precio'])    ? (float) $d['precio']  : null,
                (int) ($d['sesiones_totales'] ?? 1),
                (int) ($d['sesiones_hechas']  ?? 0),
                $d['notas'] ?? null,
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool
    {
        $sets  = ['estilo_id=?','fecha=?','precio=?','sesiones_totales=?','sesiones_hechas=?','notas=?'];
        $params = [
            !empty($d['estilo_id']) ? (int) $d['estilo_id'] : null,
            !empty($d['fecha'])     ? $d['fecha']           : null,
            !empty($d['precio'])    ? (float) $d['precio']  : null,
            (int) ($d['sesiones_totales'] ?? 1),
            (int) ($d['sesiones_hechas']  ?? 0),
            $d['notas'] ?? null,
        ];

        if (isset($d['foto_path'])) {
            $sets[]   = 'foto_path=?';
            $params[] = $d['foto_path'];
        }

        $params[] = $id;
        return $this->execute(
            'UPDATE tatuajes SET ' . implode(',', $sets) . ' WHERE id=?',
            $params
        );
    }

    public function actualizarPosicion(int $id, float $x, float $y, float $z,
                                       float $nx, float $ny, float $nz): bool
    {
        return $this->execute(
            'UPDATE tatuajes SET pos_x=?,pos_y=?,pos_z=?,normal_x=?,normal_y=?,normal_z=? WHERE id=?',
            [$x, $y, $z, $nx, $ny, $nz, $id]
        );
    }

    /** Todos los estilos para los selects */
    public function estilos(): array
    {
        return $this->query('SELECT * FROM estilos ORDER BY nombre');
    }
}
