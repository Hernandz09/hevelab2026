<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

$body = json_decode(file_get_contents('php://input'), true);
$inputDescriptor = $body['descriptor'] ?? null;

if (!is_array($inputDescriptor) || count($inputDescriptor) !== 128) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Descriptor facial inválido.']);
    exit;
}

$configFile = __DIR__ . '/../config/system_config.json';
$threshold  = 0.40;

if (file_exists($configFile)) {
    $cfg = json_decode(file_get_contents($configFile), true);
    if (isset($cfg['face_threshold']) && is_numeric($cfg['face_threshold'])) {
        $threshold = (float)$cfg['face_threshold'];
    }
}

$stmt  = $pdo->query("SELECT id, email, face_descriptor FROM usuarios WHERE face_descriptor IS NOT NULL AND verificado = 1");
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

    $sum = 0.0;
    for ($i = 0; $i < 128; $i++) {
        $diff  = (float)$inputDescriptor[$i] - (float)$stored[$i];
        $sum  += $diff * $diff;
    }
    $dist = sqrt($sum);

    if ($dist < $bestDist) {
        $bestDist  = $dist;
        $bestMatch = $user;
    }
}

if ($bestMatch && $bestDist < $threshold) {
    $userId    = $bestMatch['id'];
    $email     = $bestMatch['email'];
    $otp       = random_int(100000, 999999);

    $otpMin = 5;
    if (file_exists($configFile)) {
        $cfg2 = json_decode(file_get_contents($configFile), true);
        if (isset($cfg2['otp_expiracion_min']) && is_numeric($cfg2['otp_expiracion_min'])) {
            $otpMin = (int)$cfg2['otp_expiracion_min'];
        }
    }

    $expiracion = date("Y-m-d H:i:s", strtotime("+{$otpMin} minutes"));

    require_once __DIR__ . '/../config/mailer.php';

    $updateStmt = $pdo->prepare("UPDATE usuarios SET otp_code = ?, otp_expiracion = ? WHERE id = ?");
    $updateStmt->execute([$otp, $expiracion, $userId]);

    $_SESSION['temp_user_id'] = $userId;
    $_SESSION['otp_tipo']     = 'login';
    $_SESSION['temp_email']   = $email;

    enviarOTP($email, $otp, 'login');

    echo json_encode([
        'success'  => true,
        'redirect' => '../validater/verificar_otp.php',
        'distance' => round($bestDist, 4),
        'email'    => $email
    ]);
} else {
    echo json_encode([
        'success'  => false,
        'message'  => 'Rostro no reconocido. Verifica la iluminación o intenta con contraseña.',
        'distance' => $bestDist < PHP_FLOAT_MAX ? round($bestDist, 4) : null,
        'threshold'=> round($threshold, 2)
    ]);
}
?>
