<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Pago extends Model
{
    protected string $table = 'pagos';
    protected bool   $scoped = true;

    public const METODOS = ['efectivo', 'transferencia', 'tarjeta', 'sena', 'otro'];
    public const TIPOS   = ['ingreso', 'egreso'];

    /** KPIs en una sola query: hoy / esta semana / este mes */
    public function kpis(): array
    {
        $sid = $this->sid();
        $w   = $sid > 0 ? 'WHERE studio_id = ?' : '';
        $p   = $sid > 0 ? [$sid] : [];

        $stmt = $this->db->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN DATE(fecha) = CURDATE()                                               AND tipo='ingreso' THEN monto END), 0) AS hoy_ing,
                COALESCE(SUM(CASE WHEN DATE(fecha) = CURDATE()                                               AND tipo='egreso'  THEN monto END), 0) AS hoy_egr,
                COALESCE(SUM(CASE WHEN YEAR(fecha)=YEAR(CURDATE()) AND WEEK(fecha,1)=WEEK(CURDATE(),1)       AND tipo='ingreso' THEN monto END), 0) AS sem_ing,
                COALESCE(SUM(CASE WHEN YEAR(fecha)=YEAR(CURDATE()) AND WEEK(fecha,1)=WEEK(CURDATE(),1)       AND tipo='egreso'  THEN monto END), 0) AS sem_egr,
                COALESCE(SUM(CASE WHEN YEAR(fecha)=YEAR(CURDATE()) AND MONTH(fecha)=MONTH(CURDATE())         AND tipo='ingreso' THEN monto END), 0) AS mes_ing,
                COALESCE(SUM(CASE WHEN YEAR(fecha)=YEAR(CURDATE()) AND MONTH(fecha)=MONTH(CURDATE())         AND tipo='egreso'  THEN monto END), 0) AS mes_egr
             FROM pagos {$w}"
        );
        $stmt->execute($p);
        return $stmt->fetch() ?: [];
    }

    /** Pagos en un rango de fechas, con datos del cliente */
    public function porPeriodo(string $desde, string $hasta): array
    {
        $sid    = $this->sid();
        $extra  = $sid > 0 ? 'AND p.studio_id = ?' : '';
        $params = [$desde, $hasta];
        if ($sid > 0) $params[] = $sid;

        return $this->query(
            "SELECT p.*, c.nombre AS cliente_nombre, c.foto_perfil
             FROM pagos p
             LEFT JOIN clientes c ON c.id = p.cliente_id
             WHERE p.fecha BETWEEN ? AND ? {$extra}
             ORDER BY p.fecha DESC, p.created_at DESC",
            $params
        );
    }

    /** Totales agrupados por método de pago para un período */
    public function totalPorMetodo(string $desde, string $hasta): array
    {
        $sid    = $this->sid();
        $extra  = $sid > 0 ? 'AND studio_id = ?' : '';
        $params = [$desde, $hasta];
        if ($sid > 0) $params[] = $sid;

        return $this->query(
            "SELECT metodo, tipo, COALESCE(SUM(monto),0) AS total, COUNT(*) AS cantidad
             FROM pagos
             WHERE fecha BETWEEN ? AND ? {$extra}
             GROUP BY metodo, tipo
             ORDER BY total DESC",
            $params
        );
    }

    /** Todos los pagos de un cliente en particular */
    public function porCliente(int $clienteId): array
    {
        $sid    = $this->sid();
        $extra  = $sid > 0 ? 'AND studio_id = ?' : '';
        $params = [$clienteId];
        if ($sid > 0) $params[] = $sid;

        return $this->query(
            "SELECT * FROM pagos
             WHERE cliente_id = ? {$extra}
             ORDER BY fecha DESC, created_at DESC",
            $params
        );
    }

    public function crear(array $d): int
    {
        $sid = $this->sid();
        $this->execute(
            'INSERT INTO pagos (studio_id, cliente_id, concepto, monto, metodo, tipo, fecha, notas)
             VALUES (?,?,?,?,?,?,?,?)',
            [
                $sid ?: 1,
                !empty($d['cliente_id']) ? (int) $d['cliente_id'] : null,
                trim($d['concepto']),
                (float) $d['monto'],
                in_array($d['metodo'] ?? '', self::METODOS, true) ? $d['metodo'] : 'efectivo',
                in_array($d['tipo']   ?? '', self::TIPOS,   true) ? $d['tipo']   : 'ingreso',
                !empty($d['fecha']) ? $d['fecha'] : date('Y-m-d'),
                !empty($d['notas']) ? trim($d['notas']) : null,
            ]
        );
        return $this->lastInsertId();
    }
}
