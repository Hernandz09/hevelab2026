<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

define('CONFIG_FILE', __DIR__ . '/../config/system_config.json');

$defaults = [
    'face_threshold'     => 0.40,
    'otp_expiracion_min' => 5,
    'max_intentos_login' => 5,
    'updated_at'         => null,
    'updated_by'         => null,
];

function loadConfig(): array {
    global $defaults;
    if (!file_exists(CONFIG_FILE)) {
        return $defaults;
    }
    $data = json_decode(file_get_contents(CONFIG_FILE), true);
    return array_merge($defaults, is_array($data) ? $data : []);
}

function saveConfig(array $cfg): bool {
    return file_put_contents(CONFIG_FILE, json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

$action = $_GET['action'] ?? 'leer';

switch ($action) {

    case 'leer':
        echo json_encode(['success' => true, 'config' => loadConfig()]);
        break;

    case 'guardar':
        $body = json_decode(file_get_contents('php://input'), true);

        if (!is_array($body)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cuerpo JSON inválido.']);
            break;
        }

        $cfg = loadConfig();

        if (isset($body['face_threshold'])) {
            $t = (float)$body['face_threshold'];
            if ($t < 0.10 || $t > 1.0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'El umbral debe estar entre 0.10 y 1.00.']);
                break;
            }
            $cfg['face_threshold'] = round($t, 2);
        }

        if (isset($body['otp_expiracion_min'])) {
            $o = (int)$body['otp_expiracion_min'];
            if ($o < 1 || $o > 60) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'La expiración OTP debe estar entre 1 y 60 minutos.']);
                break;
            }
            $cfg['otp_expiracion_min'] = $o;
        }

        if (isset($body['max_intentos_login'])) {
            $m = (int)$body['max_intentos_login'];
            if ($m < 1 || $m > 20) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Los intentos máximos deben estar entre 1 y 20.']);
                break;
            }
            $cfg['max_intentos_login'] = $m;
        }

        $cfg['updated_at'] = date('Y-m-d H:i:s');
        $cfg['updated_by'] = $_SESSION['user_id'];

        if (saveConfig($cfg)) {
            echo json_encode(['success' => true, 'message' => 'Configuración guardada correctamente.', 'config' => $cfg]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al guardar. Verifica permisos de escritura en config/.']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción desconocida.']);
}
?>
