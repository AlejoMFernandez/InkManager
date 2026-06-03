<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $table = 'usuarios';

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            'SELECT * FROM usuarios WHERE email = ? LIMIT 1',
            [strtolower(trim($email))]
        );
    }

    public function create(string $nombre, string $email, string $password,
                           int $studioId = 0, string $rol = 'owner'): int
    {
        $this->execute(
            'INSERT INTO usuarios (studio_id, rol, nombre, email, password_hash) VALUES (?,?,?,?,?)',
            [
                $studioId ?: null,
                in_array($rol, ['owner','admin','staff'], true) ? $rol : 'owner',
                $nombre,
                strtolower(trim($email)),
                password_hash($password, PASSWORD_BCRYPT),
            ]
        );
        return $this->lastInsertId();
    }

    /** All users belonging to a studio, ordered owner → admin → staff then by join date. */
    public function allByStudio(int $studioId): array
    {
        return $this->query(
            "SELECT * FROM usuarios
             WHERE studio_id = ?
             ORDER BY CASE rol WHEN 'owner' THEN 0 WHEN 'admin' THEN 1 ELSE 2 END,
                      created_at ASC",
            [$studioId]
        );
    }

    public function countByStudio(int $studioId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM usuarios WHERE studio_id = ?');
        $stmt->execute([$studioId]);
        return (int) $stmt->fetchColumn();
    }

    public function updateRole(int $userId, int $studioId, string $rol): bool
    {
        return $this->execute(
            'UPDATE usuarios SET rol = ? WHERE id = ? AND studio_id = ?',
            [$rol, $userId, $studioId]
        );
    }

    public function deleteFromStudio(int $userId, int $studioId): bool
    {
        return $this->execute(
            'DELETE FROM usuarios WHERE id = ? AND studio_id = ? AND rol != ?',
            [$userId, $studioId, 'owner']   // extra safety: never delete owner via this method
        );
    }

    public function findById(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM usuarios WHERE id = ? LIMIT 1', [$id]);
    }

    public function updateProfile(int $id, string $nombre, string $email): bool
    {
        return $this->execute(
            'UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?',
            [$nombre, strtolower(trim($email)), $id]
        );
    }

    public function updatePassword(int $id, string $password): bool
    {
        return $this->execute(
            'UPDATE usuarios SET password_hash = ? WHERE id = ?',
            [password_hash($password, PASSWORD_BCRYPT), $id]
        );
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
