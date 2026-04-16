<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class UserModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function buscarUsuario(string $usuarioOEmail): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, password, email, usuario FROM usuarios WHERE usuario = ? OR email = ? LIMIT 1');
        $stmt->execute([$usuarioOEmail, $usuarioOEmail]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function buscarPorEmailCompleto(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, password, email, usuario FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function actualizarOTP(int $id, int $otp, string $expiracion): bool
    {
        $update = $this->pdo->prepare('UPDATE usuarios SET otp_code = ?, otp_expiracion = ? WHERE id = ?');
        return (bool) $update->execute([$otp, $expiracion, $id]);
    }

    public function registrarUsuario(string $usuario, string $email, string $passwordHash, string $etapa = 'Registro inicial'): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO usuarios (usuario, email, password, verificado, registro_etapa) VALUES (?, ?, ?, 0, ?)');
        $ok = $stmt->execute([$usuario, $email, $passwordHash, $etapa]);
        if (!$ok) {
            throw new \RuntimeException('No se pudo registrar el usuario.');
        }

        return (int) $this->pdo->lastInsertId();
    }

    public function actualizarEtapaRegistro(int $id, string $etapa): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET registro_etapa = ? WHERE id = ?');
        return (bool) $stmt->execute([$etapa, $id]);
    }

    public function validarOTP(int $id, string $otp): ?array
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE id = ? AND otp_code = ? AND otp_expiracion IS NOT NULL AND otp_expiracion > ?');
        $stmt->execute([$id, $otp, $now]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function limpiarOTP(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET otp_code = NULL, otp_expiracion = NULL WHERE id = ?');
        return (bool) $stmt->execute([$id]);
    }

    public function actualizarPassword(int $id, string $hash): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
        return (bool) $stmt->execute([$hash, $id]);
    }

    public function guardarDescriptorFacial(int $id, array $descriptor): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET face_descriptor = ? WHERE id = ?');
        return (bool) $stmt->execute([json_encode($descriptor, JSON_UNESCAPED_SLASHES), $id]);
    }

    public function obtenerTodosConDescriptorVerificado(): array
    {
        $stmt = $this->pdo->query('SELECT id, email, face_descriptor FROM usuarios WHERE face_descriptor IS NOT NULL AND verificado = 1');
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function marcarVerificado(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET verificado = 1, ultimo_login = NOW() WHERE id = ?');
        return (bool) $stmt->execute([$id]);
    }

    public function registrarLogin(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET ultimo_login = NOW(), verificado = 1 WHERE id = ?');
        return (bool) $stmt->execute([$id]);
    }
}
