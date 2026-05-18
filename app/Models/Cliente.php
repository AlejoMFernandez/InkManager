<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Cliente extends Model
{
    protected string $table = 'clientes';

    /** Lista paginada con búsqueda y total de tatuajes por cliente */
    public function buscar(string $term = '', int $limit = 20, int $offset = 0): array
    {
        $like   = '%' . $term . '%';
        $params = $term !== '' ? [$like, $like, $limit, $offset] : [$limit, $offset];
        $where  = $term !== '' ? 'WHERE c.nombre LIKE ? OR c.instagram LIKE ?' : '';

        return $this->query(
            "SELECT c.*, COUNT(t.id) AS total_tatuajes
             FROM clientes c
             LEFT JOIN tatuajes t ON t.cliente_id = c.id
             {$where}
             GROUP BY c.id
             ORDER BY c.nombre ASC
             LIMIT ? OFFSET ?",
            $params
        );
    }

    public function contarBusqueda(string $term = ''): int
    {
        if ($term === '') {
            return (int) $this->db->query('SELECT COUNT(*) FROM clientes')->fetchColumn();
        }
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM clientes WHERE nombre LIKE ? OR instagram LIKE ?'
        );
        $like = '%' . $term . '%';
        $stmt->execute([$like, $like]);
        return (int) $stmt->fetchColumn();
    }

    public function crear(array $d): int
    {
        $this->execute(
            'INSERT INTO clientes (nombre, instagram, telefono, primera_visita, notas)
             VALUES (?, ?, ?, ?, ?)',
            [
                trim($d['nombre']),
                $this->nullIfEmpty($d['instagram'] ?? null),
                $this->nullIfEmpty($d['telefono']  ?? null),
                $this->nullIfEmpty($d['primera_visita'] ?? null),
                $this->nullIfEmpty($d['notas']     ?? null),
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool
    {
        return $this->execute(
            'UPDATE clientes SET nombre=?, instagram=?, telefono=?, primera_visita=?, notas=?
             WHERE id=?',
            [
                trim($d['nombre']),
                $this->nullIfEmpty($d['instagram'] ?? null),
                $this->nullIfEmpty($d['telefono']  ?? null),
                $this->nullIfEmpty($d['primera_visita'] ?? null),
                $this->nullIfEmpty($d['notas']     ?? null),
                $id,
            ]
        );
    }

    /** Cliente con sus tatuajes (para la ficha) */
    public function conTatuajes(int $id): ?array
    {
        $cliente = $this->find($id);
        if (!$cliente) return null;

        $cliente['tatuajes'] = $this->query(
            'SELECT t.*, e.nombre AS estilo_nombre
             FROM tatuajes t
             LEFT JOIN estilos e ON e.id = t.estilo_id
             WHERE t.cliente_id = ?
             ORDER BY t.fecha DESC',
            [$id]
        );
        return $cliente;
    }

    private function nullIfEmpty(mixed $v): ?string
    {
        $s = trim((string) ($v ?? ''));
        return $s === '' ? null : $s;
    }
}
