<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Galeria extends Model
{
    protected string $table  = 'galeria';
    protected bool   $scoped = true;

    /** Estilos disponibles — orden para UI */
    public const ESTILOS = [
        'tradicional', 'neo-tradicional', 'realismo', 'blackwork',
        'watercolor',  'linework',        'geometric', 'japonés',
        'chicano',     'otro',
    ];

    // ── Consultas ─────────────────────────────────────────────────────────────

    /** Todas las fotos, con nombre de cliente, filtradas por estilo si se pasa. */
    public function todos(string $estilo = ''): array
    {
        $sid    = $this->sid();
        $where  = ['1=1'];
        $params = [];

        if ($sid > 0)    { $where[] = 'g.studio_id = ?';  $params[] = $sid; }
        if ($estilo !== '') { $where[] = 'g.estilo = ?';   $params[] = $estilo; }

        return $this->query(
            "SELECT g.*, c.nombre AS cliente_nombre
             FROM galeria g
             LEFT JOIN clientes c ON c.id = g.cliente_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY g.created_at DESC",
            $params
        );
    }

    // ── Escritura ─────────────────────────────────────────────────────────────

    public function crear(array $d): int
    {
        $sid = $this->sid();
        $this->execute(
            'INSERT INTO galeria (studio_id, cliente_id, turno_id, titulo, estilo, archivo)
             VALUES (?,?,?,?,?,?)',
            [
                $sid ?: 1,
                !empty($d['cliente_id']) ? (int) $d['cliente_id'] : null,
                !empty($d['turno_id'])   ? (int) $d['turno_id']   : null,
                trim($d['titulo'] ?? '') !== '' ? trim($d['titulo']) : null,
                !empty($d['estilo']) && in_array($d['estilo'], self::ESTILOS, true)
                    ? $d['estilo'] : null,
                $d['archivo'],
            ]
        );
        return (int) $this->lastInsertId();
    }

    public function borrar(int $id): void
    {
        $sid    = $this->sid();
        $extra  = $sid > 0 ? 'AND studio_id = ?' : '';
        $params = $sid > 0 ? [$id, $sid] : [$id];
        $this->execute("DELETE FROM galeria WHERE id = ? {$extra}", $params);
    }
}
