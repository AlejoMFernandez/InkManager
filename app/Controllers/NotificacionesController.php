<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Cliente;

class NotificacionesController extends Controller
{
    /**
     * GET /api/notificaciones
     * Devuelve un JSON con grupos de notificaciones para el panel de la campana.
     */
    public function feed(array $params = []): void
    {
        $sid    = Auth::studioId();
        $db     = Database::get();
        $groups = [];
        $total  = 0;

        // ── 1. Turnos de hoy ──────────────────────────────────────────────
        $sidW    = $sid > 0 ? 'AND tu.studio_id = ?' : '';
        $tParams = $sid > 0 ? [$sid] : [];

        $stmt = $db->prepare(
            "SELECT tu.id, tu.fecha_inicio, tu.duracion_min, tu.estado,
                    c.nombre AS cliente_nombre, c.foto_perfil
             FROM turnos tu
             JOIN clientes c ON c.id = tu.cliente_id
             WHERE DATE(tu.fecha_inicio) = CURDATE()
               AND tu.estado IN ('agendado','confirmado')
               {$sidW}
             ORDER BY tu.fecha_inicio ASC"
        );
        $stmt->execute($tParams);
        $turnosHoy = $stmt->fetchAll();

        if ($turnosHoy) {
            $items = [];
            foreach ($turnosHoy as $t) {
                $hora    = date('H:i', strtotime($t['fecha_inicio']));
                $dur     = (int) $t['duracion_min'];
                $items[] = [
                    'id'       => (int) $t['id'],
                    'label'    => $t['cliente_nombre'],
                    'sublabel' => $hora . ' · ' . $dur . ' min',
                    'url'      => BASE_URL . '/turnos/' . $t['id'],
                    'avatar'   => $t['foto_perfil']
                        ? PUBLIC_URL . '/assets/uploads/avatars/' . $t['foto_perfil']
                        : null,
                ];
            }
            $count   = count($items);
            $total  += $count;
            $groups[] = [
                'type'  => 'turnos_hoy',
                'label' => 'Turnos de hoy',
                'color' => 'blue',
                'count' => $count,
                'items' => $items,
            ];
        }

        // ── 2. Presupuestos enviados sin respuesta ≥ 7 días ───────────────
        $pSidW   = $sid > 0 ? 'AND p.studio_id = ?' : '';
        $pParams = $sid > 0 ? [$sid] : [];

        $stmt = $db->prepare(
            "SELECT p.id, p.numero, p.titulo, p.fecha,
                    c.nombre AS cliente_nombre
             FROM presupuestos p
             JOIN clientes c ON c.id = p.cliente_id
             WHERE p.estado = 'enviado'
               AND DATEDIFF(CURDATE(), p.fecha) >= 7
               {$pSidW}
             ORDER BY p.fecha ASC"
        );
        $stmt->execute($pParams);
        $presupuestos = $stmt->fetchAll();

        if ($presupuestos) {
            $items = [];
            foreach ($presupuestos as $p) {
                $dias    = (int) round((time() - strtotime($p['fecha'])) / 86400);
                $items[] = [
                    'id'       => (int) $p['id'],
                    'label'    => $p['numero'] . ' — ' . $p['cliente_nombre'],
                    'sublabel' => 'Sin respuesta hace ' . $dias . ' día' . ($dias !== 1 ? 's' : ''),
                    'url'      => BASE_URL . '/presupuestos/' . $p['id'],
                    'avatar'   => null,
                ];
            }
            $count   = count($items);
            $total  += $count;
            $groups[] = [
                'type'  => 'presupuestos',
                'label' => 'Presupuestos sin respuesta',
                'color' => 'yellow',
                'count' => $count,
                'items' => $items,
            ];
        }

        // ── 3. Cumpleaños en los próximos 7 días ──────────────────────────
        $cumples = (new Cliente())->cumpleanosProximos(7);
        if ($cumples) {
            $items = [];
            foreach ($cumples as $c) {
                $dias  = (int) $c['dias_falta'];
                $when  = match (true) {
                    $dias === 0 => '🎂 ¡Hoy!',
                    $dias === 1 => 'Mañana',
                    default     => "En {$dias} días",
                };
                $items[] = [
                    'id'       => (int) $c['id'],
                    'label'    => $c['nombre'],
                    'sublabel' => $when,
                    'url'      => BASE_URL . '/clientes/' . $c['id'],
                    'avatar'   => $c['foto_perfil']
                        ? PUBLIC_URL . '/assets/uploads/avatars/' . $c['foto_perfil']
                        : null,
                ];
            }
            $count   = count($items);
            $total  += $count;
            $groups[] = [
                'type'  => 'cumpleanos',
                'label' => 'Cumpleaños próximos',
                'color' => 'pink',
                'count' => $count,
                'items' => $items,
            ];
        }

        $this->json(['total' => $total, 'groups' => $groups]);
    }
}
