<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Cliente extends Model
{
    protected string $table  = 'clientes';
    protected bool   $scoped = true;

    /** Lista paginada con búsqueda, total de tatuajes y tags — scoped by studio */
    public function buscar(string $term = '', int $limit = 20, int $offset = 0, int $etiquetaId = 0): array
    {
        $sid        = $this->sid();
        $conditions = [];
        $params     = [];

        if ($sid > 0) {
            $conditions[] = 'c.studio_id = ?';
            $params[]     = $sid;
        }
        if ($term !== '') {
            $conditions[] = '(c.nombre LIKE ? OR c.instagram LIKE ?)';
            $like         = '%' . $term . '%';
            $params[]     = $like;
            $params[]     = $like;
        }
        if ($etiquetaId > 0) {
            $conditions[] = 'EXISTS (SELECT 1 FROM cliente_etiquetas ce WHERE ce.cliente_id = c.id AND ce.etiqueta_id = ?)';
            $params[]     = $etiquetaId;
        }

        $where    = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $params[] = $limit;
        $params[] = $offset;

        return $this->query(
            "SELECT c.*,
                    COUNT(DISTINCT t.id) AS total_tatuajes,
                    (SELECT GROUP_CONCAT(et.id   ORDER BY et.nombre SEPARATOR ',')
                     FROM cliente_etiquetas ce2
                     JOIN etiquetas et ON et.id = ce2.etiqueta_id
                     WHERE ce2.cliente_id = c.id) AS tag_ids,
                    (SELECT GROUP_CONCAT(et.nombre ORDER BY et.nombre SEPARATOR '||')
                     FROM cliente_etiquetas ce3
                     JOIN etiquetas et ON et.id = ce3.etiqueta_id
                     WHERE ce3.cliente_id = c.id) AS tag_nombres,
                    (SELECT GROUP_CONCAT(et.color  ORDER BY et.nombre SEPARATOR ',')
                     FROM cliente_etiquetas ce4
                     JOIN etiquetas et ON et.id = ce4.etiqueta_id
                     WHERE ce4.cliente_id = c.id) AS tag_colores
             FROM clientes c
             LEFT JOIN tatuajes t ON t.cliente_id = c.id
             {$where}
             GROUP BY c.id
             ORDER BY c.nombre ASC
             LIMIT ? OFFSET ?",
            $params
        );
    }

    public function contarBusqueda(string $term = '', int $etiquetaId = 0): int
    {
        $sid        = $this->sid();
        $conditions = [];
        $params     = [];

        if ($sid > 0) {
            $conditions[] = 'studio_id = ?';
            $params[]     = $sid;
        }
        if ($term !== '') {
            $like         = '%' . $term . '%';
            $conditions[] = '(nombre LIKE ? OR instagram LIKE ?)';
            $params[]     = $like;
            $params[]     = $like;
        }
        if ($etiquetaId > 0) {
            $conditions[] = 'EXISTS (SELECT 1 FROM cliente_etiquetas ce WHERE ce.cliente_id = id AND ce.etiqueta_id = ?)';
            $params[]     = $etiquetaId;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $stmt  = $this->db->prepare("SELECT COUNT(*) FROM clientes {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function crear(array $d): int
    {
        $sid = $this->sid();
        $this->execute(
            'INSERT INTO clientes (studio_id, nombre, instagram, telefono, primera_visita, fecha_nacimiento, notas, genero)
             VALUES (?,?,?,?,?,?,?,?)',
            [
                $sid ?: 1,
                trim($d['nombre']),
                $this->nullIfEmpty($d['instagram']        ?? null),
                $this->nullIfEmpty($d['telefono']         ?? null),
                $this->nullIfEmpty($d['primera_visita']   ?? null),
                $this->nullIfEmpty($d['fecha_nacimiento'] ?? null),
                $this->nullIfEmpty($d['notas']            ?? null),
                $this->sanitizeGenero($d['genero']        ?? 'masculino'),
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizarAvatar(int $id, ?string $path): bool
    {
        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND studio_id = ?' : '';
        $params    = [$path, $id];
        if ($sid > 0) $params[] = $sid;

        return $this->execute(
            "UPDATE clientes SET foto_perfil = ? WHERE id = ? {$andStudio}",
            $params
        );
    }

    public function actualizar(int $id, array $d): bool
    {
        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND studio_id = ?' : '';
        $params    = [
            trim($d['nombre']),
            $this->nullIfEmpty($d['instagram']        ?? null),
            $this->nullIfEmpty($d['telefono']         ?? null),
            $this->nullIfEmpty($d['primera_visita']   ?? null),
            $this->nullIfEmpty($d['fecha_nacimiento'] ?? null),
            $this->nullIfEmpty($d['notas']            ?? null),
            $this->sanitizeGenero($d['genero']        ?? 'masculino'),
            $id,
        ];
        if ($sid > 0) $params[] = $sid;

        return $this->execute(
            "UPDATE clientes SET nombre=?, instagram=?, telefono=?, primera_visita=?, fecha_nacimiento=?, notas=?, genero=?
             WHERE id=? {$andStudio}",
            $params
        );
    }

    /** Clientes con cumpleaños en los próximos $days días (scoped) */
    public function cumpleanosProximos(int $days = 7): array
    {
        $sid    = $this->sid();
        $extra  = '';
        $params = [$days, $days];
        if ($sid > 0) { $extra = 'AND studio_id = ?'; $params[] = $sid; }

        return $this->query(
            "SELECT id, nombre, instagram, telefono, fecha_nacimiento, foto_perfil,
                    CASE
                        WHEN DATE(CONCAT(YEAR(CURDATE()),'-',LPAD(MONTH(fecha_nacimiento),2,'0'),'-',LPAD(DAY(fecha_nacimiento),2,'0'))) >= CURDATE()
                        THEN DATEDIFF(DATE(CONCAT(YEAR(CURDATE()),'-',LPAD(MONTH(fecha_nacimiento),2,'0'),'-',LPAD(DAY(fecha_nacimiento),2,'0'))), CURDATE())
                        ELSE DATEDIFF(DATE(CONCAT(YEAR(CURDATE())+1,'-',LPAD(MONTH(fecha_nacimiento),2,'0'),'-',LPAD(DAY(fecha_nacimiento),2,'0'))), CURDATE())
                    END AS dias_falta
             FROM clientes
             WHERE fecha_nacimiento IS NOT NULL
               AND (
                 DATE(CONCAT(YEAR(CURDATE()),'-',LPAD(MONTH(fecha_nacimiento),2,'0'),'-',LPAD(DAY(fecha_nacimiento),2,'0')))
                   BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                 OR
                 DATE(CONCAT(YEAR(CURDATE())+1,'-',LPAD(MONTH(fecha_nacimiento),2,'0'),'-',LPAD(DAY(fecha_nacimiento),2,'0')))
                   BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
               )
               {$extra}
             ORDER BY dias_falta ASC",
            $params
        );
    }

    /** Cliente con sus tatuajes (para la ficha) — scoped */
    public function conTatuajes(int $id): ?array
    {
        $cliente = $this->find($id); // already scoped via base find()
        if (!$cliente) return null;

        $sid    = $this->sid();
        $extra  = '';
        $params = [$id];
        if ($sid > 0) {
            $extra    = 'AND t.studio_id = ?';
            $params[] = $sid;
        }

        $cliente['tatuajes'] = $this->query(
            "SELECT t.*, e.nombre AS estilo_nombre
             FROM tatuajes t
             LEFT JOIN estilos e ON e.id = t.estilo_id
             WHERE t.cliente_id = ? {$extra}
             ORDER BY t.fecha DESC",
            $params
        );
        return $cliente;
    }

    private function sanitizeGenero(mixed $v): string
    {
        return in_array($v, ['masculino', 'femenino', 'otro'], true) ? (string) $v : 'masculino';
    }

    private function nullIfEmpty(mixed $v): ?string
    {
        $s = trim((string) ($v ?? ''));
        return $s === '' ? null : $s;
    }
}
