<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO    $db;
    protected string $table;
    protected string $primaryKey = 'id';

    /**
     * Set to true in models that have a `studio_id` column.
     * When true, find / all / delete automatically scope to Auth::studioId().
     */
    protected bool $scoped = false;

    public function __construct()
    {
        $this->db = Database::get();
    }

    // ── Studio scoping helper ─────────────────────────────────────────────

    protected function sid(): int
    {
        return Auth::studioId();
    }

    // ── Generic CRUD ─────────────────────────────────────────────────────

    public function find(int $id): ?array
    {
        if ($this->scoped && ($sid = $this->sid()) > 0) {
            $stmt = $this->db->prepare(
                "SELECT * FROM `{$this->table}`
                 WHERE `{$this->primaryKey}` = ? AND `studio_id` = ? LIMIT 1"
            );
            $stmt->execute([$id, $sid]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1"
            );
            $stmt->execute([$id]);
        }
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        if ($this->scoped && ($sid = $this->sid()) > 0) {
            $stmt = $this->db->prepare(
                "SELECT * FROM `{$this->table}`
                 WHERE `studio_id` = ?
                 ORDER BY `{$orderBy}` {$direction}"
            );
            $stmt->execute([$sid]);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->query(
            "SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$direction}"
        );
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool
    {
        if ($this->scoped && ($sid = $this->sid()) > 0) {
            $stmt = $this->db->prepare(
                "DELETE FROM `{$this->table}`
                 WHERE `{$this->primaryKey}` = ? AND `studio_id` = ?"
            );
            return $stmt->execute([$id, $sid]);
        }

        $stmt = $this->db->prepare(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?"
        );
        return $stmt->execute([$id]);
    }

    // ── Low-level helpers ─────────────────────────────────────────────────

    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    protected function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }
}
