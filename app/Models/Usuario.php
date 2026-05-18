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

    public function create(string $nombre, string $email, string $password): int
    {
        $this->execute(
            'INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)',
            [$nombre, strtolower(trim($email)), password_hash($password, PASSWORD_BCRYPT)]
        );
        return $this->lastInsertId();
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
