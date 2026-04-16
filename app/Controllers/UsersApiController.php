<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\PdoService;

final class UsersApiController
{
    public function handle(Request $request, string $action = 'listar'): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $publicActions = ['iniciar_registro', 'actualizar_etapa', 'cancelar_registro'];
        if (!isset($_SESSION['user_id']) && !in_array($action, $publicActions, true)) {
            Response::json(['success' => false, 'message' => 'No autorizado.'], 401);
        }

        $pdo = PdoService::make();
        $body = $request->allBody();

        switch ($action) {
            case 'listar':
                $stmt = $pdo->query('
                    SELECT id, usuario, email, verificado,
                    (face_descriptor IS NOT NULL) AS tiene_facial,
                    ultimo_login, created_at
                    FROM usuarios
                    ORDER BY created_at DESC
                ');
                Response::json(['success' => true, 'usuarios' => $stmt->fetchAll()]);

            case 'crear':
                $usuario = trim((string) ($body['usuario'] ?? ''));
                $email = trim((string) ($body['email'] ?? ''));
                $password = (string) ($body['password'] ?? '');
                if ($usuario === '' || $email === '' || $password === '') {
                    Response::json(['success' => false, 'message' => 'Todos los campos son requeridos.'], 400);
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Response::json(['success' => false, 'message' => 'Email inválido.'], 400);
                }
                $dup = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? OR usuario = ?');
                $dup->execute([$email, $usuario]);
                if ($dup->fetch()) {
                    Response::json(['success' => false, 'message' => 'El email o nombre de usuario ya existe.'], 409);
                }
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins = $pdo->prepare('INSERT INTO usuarios (usuario, email, password, verificado) VALUES (?, ?, ?, 1)');
                $ins->execute([$usuario, $email, $hash]);
                Response::json(['success' => true, 'message' => 'Usuario creado correctamente.', 'id' => (int) $pdo->lastInsertId()]);

            case 'editar':
                $id = (int) ($body['id'] ?? 0);
                $usuario = trim((string) ($body['usuario'] ?? ''));
                $email = trim((string) ($body['email'] ?? ''));
                if ($id <= 0 || $usuario === '' || $email === '') {
                    Response::json(['success' => false, 'message' => 'Datos incompletos.'], 400);
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Response::json(['success' => false, 'message' => 'Email inválido.'], 400);
                }
                $dup = $pdo->prepare('SELECT id FROM usuarios WHERE (email = ? OR usuario = ?) AND id != ?');
                $dup->execute([$email, $usuario, $id]);
                if ($dup->fetch()) {
                    Response::json(['success' => false, 'message' => 'El email o nombre de usuario ya está en uso.'], 409);
                }
                $upd = $pdo->prepare('UPDATE usuarios SET usuario = ?, email = ? WHERE id = ?');
                $upd->execute([$usuario, $email, $id]);
                Response::json(['success' => true, 'message' => 'Usuario actualizado correctamente.']);

            case 'eliminar':
                $id = (int) ($body['id'] ?? 0);
                if ($id <= 0) {
                    Response::json(['success' => false, 'message' => 'ID de usuario requerido.'], 400);
                }
                if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
                    Response::json(['success' => false, 'message' => 'No puedes eliminar tu propio usuario.'], 403);
                }
                $del = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
                $del->execute([$id]);
                Response::json(['success' => true, 'message' => 'Usuario eliminado correctamente.']);

            case 'toggle_verificado':
                $id = (int) ($body['id'] ?? 0);
                $verificado = (int) ($body['verificado'] ?? 0);
                if ($id <= 0) {
                    Response::json(['success' => false, 'message' => 'ID de usuario requerido.'], 400);
                }
                $upd = $pdo->prepare('UPDATE usuarios SET verificado = ? WHERE id = ?');
                $upd->execute([$verificado ? 1 : 0, $id]);
                Response::json(['success' => true, 'message' => $verificado ? 'Usuario verificado.' : 'Usuario suspendido.']);

            case 'resetear_facial':
                $id = (int) ($body['id'] ?? 0);
                if ($id <= 0) {
                    Response::json(['success' => false, 'message' => 'ID de usuario requerido.'], 400);
                }
                $upd = $pdo->prepare('UPDATE usuarios SET face_descriptor = NULL WHERE id = ?');
                $upd->execute([$id]);
                Response::json(['success' => true, 'message' => 'Descriptor facial eliminado.']);

            case 'guardar_facial':
                $id = (int) ($body['id'] ?? 0);
                $descriptor = $body['descriptor'] ?? null;
                if ($id <= 0) {
                    Response::json(['success' => false, 'message' => 'ID de usuario requerido.'], 400);
                }
                if (!is_array($descriptor) || count($descriptor) !== 128) {
                    Response::json(['success' => false, 'message' => 'Descriptor facial inválido.'], 400);
                }
                $encoded = json_encode(array_values($descriptor), JSON_UNESCAPED_SLASHES);
                if (!is_string($encoded) || $encoded === '' || $encoded === 'null') {
                    Response::json(['success' => false, 'message' => 'No se pudo procesar el descriptor facial.'], 400);
                }
                $upd = $pdo->prepare('UPDATE usuarios SET face_descriptor = ? WHERE id = ?');
                $ok = $upd->execute([$encoded, $id]);
                Response::json(['success' => (bool) $ok, 'message' => 'Biometría facial registrada.']);

            case 'actualizar_etapa':
                $id = (int) ($body['id'] ?? 0);
                $etapa = trim((string) ($body['etapa'] ?? ''));
                if ($id <= 0 || $etapa === '') {
                    Response::json(['success' => false, 'message' => 'IDs o etapa faltantes.'], 400);
                }
                $upd = $pdo->prepare('UPDATE usuarios SET registro_etapa = ? WHERE id = ?');
                $ok = $upd->execute([$etapa, $id]);
                Response::json(['success' => (bool) $ok]);

            case 'cancelar_registro':
                $id = (int) ($body['id'] ?? 0);
                if ($id <= 0) {
                    Response::json(['success' => false, 'message' => 'ID inválido.'], 400);
                }
                $del = $pdo->prepare('DELETE FROM usuarios WHERE id = ? AND verificado = 0');
                $ok = $del->execute([$id]);
                Response::json(['success' => (bool) $ok]);

            default:
                Response::json(['success' => false, 'message' => 'Acción desconocida.'], 400);
        }
    }
}
