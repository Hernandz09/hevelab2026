<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

$action = $_GET['action'] ?? 'listar';

switch ($action) {

    case 'listar':
        $stmt = $pdo->query("
            SELECT
                id,
                usuario,
                email,
                verificado,
                (face_descriptor IS NOT NULL) AS tiene_facial,
                ultimo_login,
                created_at
            FROM usuarios
            ORDER BY created_at DESC
        ");
        echo json_encode(['success' => true, 'usuarios' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    case 'crear':
        $body     = json_decode(file_get_contents('php://input'), true);
        $usuario  = trim($body['usuario']  ?? '');
        $email    = trim($body['email']    ?? '');
        $password = $body['password']       ?? '';

        if (!$usuario || !$email || !$password) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos.']);
            break;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email inválido.']);
            break;
        }

        $dup = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? OR usuario = ?");
        $dup->execute([$email, $usuario]);
        if ($dup->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'El email o nombre de usuario ya existe.']);
            break;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $ins  = $pdo->prepare("INSERT INTO usuarios (usuario, email, password, verificado) VALUES (?, ?, ?, 1)");
        $ins->execute([$usuario, $email, $hash]);
        echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente.', 'id' => $pdo->lastInsertId()]);
        break;

    case 'editar':
        $body    = json_decode(file_get_contents('php://input'), true);
        $id      = (int)($body['id']      ?? 0);
        $usuario = trim($body['usuario']  ?? '');
        $email   = trim($body['email']    ?? '');

        if (!$id || !$usuario || !$email) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            break;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email inválido.']);
            break;
        }

        $dup = $pdo->prepare("SELECT id FROM usuarios WHERE (email = ? OR usuario = ?) AND id != ?");
        $dup->execute([$email, $usuario, $id]);
        if ($dup->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'El email o nombre de usuario ya está en uso.']);
            break;
        }

        $upd = $pdo->prepare("UPDATE usuarios SET usuario = ?, email = ? WHERE id = ?");
        $upd->execute([$usuario, $email, $id]);
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
        break;

    case 'eliminar':
        $body = json_decode(file_get_contents('php://input'), true);
        $id   = (int)($body['id'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
            break;
        }
        if ($id === (int)$_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propio usuario.']);
            break;
        }

        $del = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $del->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente.']);
        break;

    case 'resetear_facial':
        $body = json_decode(file_get_contents('php://input'), true);
        $id   = (int)($body['id'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
            break;
        }

        $upd = $pdo->prepare("UPDATE usuarios SET face_descriptor = NULL WHERE id = ?");
        $upd->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Descriptor facial eliminado.']);
        break;

    case 'toggle_verificado':
        $body       = json_decode(file_get_contents('php://input'), true);
        $id         = (int)($body['id']         ?? 0);
        $verificado = (int)($body['verificado'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
            break;
        }

        $upd = $pdo->prepare("UPDATE usuarios SET verificado = ? WHERE id = ?");
        $upd->execute([$verificado ? 1 : 0, $id]);
        echo json_encode(['success' => true, 'message' => $verificado ? 'Usuario verificado.' : 'Usuario suspendido.']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción desconocida.']);
}
?>
