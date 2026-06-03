<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class BusquedaController extends Controller
{
    private const LIMIT = 5;

    /**
     * GET /api/busqueda?q=…
     * Devuelve grupos de resultados para el command palette.
     */
    public function search(array $params = []): void
    {
        $q = trim($_GET['q'] ?? '');

        if (mb_strlen($q) < 2) {
            $this->json(['groups' => [], 'total' => 0]);
            return;
        }

        $sid  = Auth::studioId();
        $db   = Database::get();
        $like = '%' . $q . '%';
        $groups = [];
        $total  = 0;

        // ── Clientes ──────────────────────────────────────────────────────
        $sidW    = $sid > 0 ? 'AND studio_id = ?' : '';
        $cParams = $sid > 0
            ? [$like, $like, $like, $sid]
            : [$like, $like, $like];

        $stmt = $db->prepare(
            "SELECT id, nombre, instagram, telefono, foto_perfil
             FROM clientes
             WHERE (nombre LIKE ? OR instagram LIKE ? OR telefono LIKE ?)
               {$sidW}
             ORDER BY nombre ASC
             LIMIT " . self::LIMIT
        );
        $stmt->execute($cParams);
        $clientes = $stmt->fetchAll();

        if ($clientes) {
            $items = [];
            foreach ($clientes as $c) {
                $meta    = [];
                if (!empty($c['instagram'])) $meta[] = $c['instagram'];
                if (!empty($c['telefono']))  $meta[] = $c['telefono'];
                $items[] = [
                    'label'    => $c['nombre'],
                    'sublabel' => implode(' · ', $meta),
                    'url'      => BASE_URL . '/clientes/' . $c['id'],
                    'avatar'   => $c['foto_perfil']
                        ? PUBLIC_URL . '/assets/uploads/avatars/' . $c['foto_perfil']
                        : null,
                    'initial'  => mb_strtoupper(mb_substr($c['nombre'], 0, 1)),
                ];
            }
            $total += count($items);
            $groups[] = ['type' => 'clientes', 'label' => 'Clientes', 'items' => $items];
        }

        // ── Turnos ────────────────────────────────────────────────────────
        $tSidW   = $sid > 0 ? 'AND tu.studio_id = ?' : '';
        $tParams = $sid > 0
            ? [$like, $like, $sid]
            : [$like, $like];

        $stmt = $db->prepare(
            "SELECT tu.id, tu.fecha_inicio, tu.estado, tu.duracion_min,
                    c.nombre AS cliente_nombre, c.foto_perfil
             FROM turnos tu
             JOIN clientes c ON c.id = tu.cliente_id
             WHERE (c.nombre LIKE ? OR tu.notas LIKE ?)
               {$tSidW}
             ORDER BY tu.fecha_inicio DESC
             LIMIT " . self::LIMIT
        );
        $stmt->execute($tParams);
        $turnos = $stmt->fetchAll();

        if ($turnos) {
            $items = [];
            foreach ($turnos as $t) {
                $fecha   = date('d/m/Y H:i', strtotime($t['fecha_inicio']));
                $items[] = [
                    'label'    => $t['cliente_nombre'],
                    'sublabel' => $fecha . ' · ' . ucfirst($t['estado']),
                    'url'      => BASE_URL . '/turnos/' . $t['id'],
                    'avatar'   => $t['foto_perfil']
                        ? PUBLIC_URL . '/assets/uploads/avatars/' . $t['foto_perfil']
                        : null,
                    'initial'  => mb_strtoupper(mb_substr($t['cliente_nombre'], 0, 1)),
                    'badge'    => $t['estado'],
                ];
            }
            $total += count($items);
            $groups[] = ['type' => 'turnos', 'label' => 'Turnos', 'items' => $items];
        }

        // ── Presupuestos ──────────────────────────────────────────────────
        $pSidW   = $sid > 0 ? 'AND p.studio_id = ?' : '';
        $pParams = $sid > 0
            ? [$like, $like, $like, $sid]
            : [$like, $like, $like];

        $stmt = $db->prepare(
            "SELECT p.id, p.numero, p.titulo, p.estado, p.fecha,
                    c.nombre AS cliente_nombre
             FROM presupuestos p
             JOIN clientes c ON c.id = p.cliente_id
             WHERE (p.numero LIKE ? OR p.titulo LIKE ? OR c.nombre LIKE ?)
               {$pSidW}
             ORDER BY p.fecha DESC
             LIMIT " . self::LIMIT
        );
        $stmt->execute($pParams);
        $presupuestos = $stmt->fetchAll();

        if ($presupuestos) {
            $items = [];
            foreach ($presupuestos as $p) {
                $items[] = [
                    'label'    => $p['titulo'] ?: $p['numero'],
                    'sublabel' => $p['numero'] . ' · ' . $p['cliente_nombre'],
                    'url'      => BASE_URL . '/presupuestos/' . $p['id'],
                    'avatar'   => null,
                    'initial'  => '$',
                    'badge'    => $p['estado'],
                ];
            }
            $total += count($items);
            $groups[] = ['type' => 'presupuestos', 'label' => 'Presupuestos', 'items' => $items];
        }

        $this->json(['groups' => $groups, 'total' => $total]);
    }
}
