<?php
/**
 * api/face_login.php
 * Recibe descriptor facial 128D, busca coincidencia en BD y crea sesión.
 *
 * POST body (JSON): { "descriptor": [f32 x 128] }
 * Response (JSON):  { "success": bool, "redirect": url, "message": str }
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

// Leer body JSON
$body = json_decode(file_get_contents('php://input'), true);
$inputDescriptor = $body['descriptor'] ?? null;

// Validar descriptor
if (!is_array($inputDescriptor) || count($inputDescriptor) !== 128) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Descriptor facial inválido.']);
    exit;
}

// Threshold: distancia euclidiana máxima para considerar coincidencia
const THRESHOLD = 0.52;

// Obtener todos los usuarios con descriptor facial
$stmt = $pdo->query("SELECT id, email, face_descriptor FROM usuarios WHERE face_descriptor IS NOT NULL");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$users) {
    echo json_encode(['success' => false, 'message' => 'No hay usuarios con reconocimiento facial registrado.']);
    exit;
}

$bestMatch = null;
$bestDist  = PHP_FLOAT_MAX;

foreach ($users as $user) {
    $stored = json_decode($user['face_descriptor'], true);
    if (!is_array($stored) || count($stored) !== 128) continue;

    // Distancia euclidiana entre descriptores
    $sum = 0.0;
    for ($i = 0; $i < 128; $i++) {
        $diff = (float)$inputDescriptor[$i] - (float)$stored[$i];
        $sum += $diff * $diff;
    }
    $dist = sqrt($sum);

    if ($dist < $bestDist) {
        $bestDist  = $dist;
        $bestMatch = $user;
    }
}

if ($bestMatch && $bestDist < THRESHOLD) {
    // Coincidencia encontrada — iniciar flujo OTP
    $userId = $bestMatch['id'];
    $email  = $bestMatch['email'];
    
    // Generar OTP
    $otp = random_int(100000, 999999);
    $expiracion = date("Y-m-d H:i:s", strtotime('+5 minutes'));

    // Actualizar OTP en BD (usamos PDO directo porque authController no está disponible aquí)
    require_once __DIR__ . '/../config/mailer.php';
    $updateStmt = $pdo->prepare("UPDATE usuarios SET otp_code = ?, otp_expiracion = ? WHERE id = ?");
    $updateStmt->execute([$otp, $expiracion, $userId]);

    // Variables de sesión para la vista OTP
    $_SESSION['temp_user_id'] = $userId;
    $_SESSION['otp_tipo']     = 'login';
    $_SESSION['temp_email']   = $email;

    // Enviar correo
    enviarOTP($email, $otp, 'login');

    echo json_encode([
        'success'  => true,
        'redirect' => '../validater/verificar_otp.php',
        'distance' => round($bestDist, 4),
        'email'    => $email
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Rostro no reconocido. Verifica la iluminación o intenta con contraseña.',
        'distance' => $bestDist < PHP_FLOAT_MAX ? round($bestDist, 4) : null
    ]);
}
?>
