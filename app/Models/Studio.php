<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Studio extends Model
{
    protected string $table  = 'studios';
    protected bool   $scoped = false; // studios is not itself scoped by studio_id

    // ── Plan definitions ─────────────────────────────────────────────────
    public const PLANS = [
        'free' => ['label' => 'Free', 'max_clientes' => 50,             'max_usuarios' => 1],
        'pro'  => ['label' => 'Pro',  'max_clientes' => PHP_INT_MAX,    'max_usuarios' => 5],
    ];

    // ── Lookups ───────────────────────────────────────────────────────────

    public function findBySlug(string $slug): ?array
    {
        return $this->queryOne(
            'SELECT * FROM studios WHERE slug = ? LIMIT 1',
            [$slug]
        );
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function crear(string $nombre, string $slug, int $ownerId = 0): int
    {
        $this->execute(
            'INSERT INTO studios (nombre, slug, owner_id) VALUES (?, ?, ?)',
            [$nombre, $slug, $ownerId ?: null]
        );
        return $this->lastInsertId();
    }

    // ── Plan helpers ──────────────────────────────────────────────────────

    public function contarClientes(int $studioId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM clientes WHERE studio_id = ?');
        $stmt->execute([$studioId]);
        return (int) $stmt->fetchColumn();
    }

    public function planLimits(int $studioId): array
    {
        $studio = $this->find($studioId);
        $plan   = $studio['plan'] ?? 'free';
        return self::PLANS[$plan] ?? self::PLANS['free'];
    }

    public function planReached(int $studioId, string $resource = 'clientes'): bool
    {
        $limits = $this->planLimits($studioId);
        $max    = $limits['max_' . $resource] ?? PHP_INT_MAX;
        if ($max >= PHP_INT_MAX) return false;

        $count = match ($resource) {
            'clientes'  => $this->contarClientes($studioId),
            default     => 0,
        };

        return $count >= $max;
    }

    // ── Slug generator ────────────────────────────────────────────────────

    public static function slugify(string $text): string
    {
        $map  = [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n',
            'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ü'=>'u','Ñ'=>'n',
        ];
        $slug = mb_strtolower(strtr(trim($text), $map));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug ?? '', '-') ?: 'studio';
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function updateSettings(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE studios
             SET nombre = ?, telefono = ?, instagram = ?, website = ?, direccion = ?
             WHERE id = ?',
            [
                $data['nombre'],
                $data['telefono']  ?: null,
                $data['instagram'] ?: null,
                $data['website']   ?: null,
                $data['direccion'] ?: null,
                $id,
            ]
        );
    }

    /** Ensure uniqueness — appends -2, -3, … if slug already exists */
    public function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i    = 2;
        while ($this->findBySlug($slug)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
