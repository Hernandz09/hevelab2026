<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;

final class PdoService
{
    public static function make(): PDO
    {
        $cfg = require __DIR__ . '/../../config/database.php';

        $driver = (string) ($cfg['driver'] ?? 'mysql');
        $host = (string) ($cfg['host'] ?? 'localhost');
        $port = (int) ($cfg['port'] ?? 3306);
        $db = (string) ($cfg['database'] ?? '');
        $charset = (string) ($cfg['charset'] ?? 'utf8mb4');
        $user = (string) ($cfg['username'] ?? '');
        $pass = (string) ($cfg['password'] ?? '');

        $dsn = "{$driver}:host={$host};port={$port};dbname={$db};charset={$charset}";

        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Error de conexión a la base de datos: ' . $e->getMessage());
        }

        return $pdo;
    }
}
