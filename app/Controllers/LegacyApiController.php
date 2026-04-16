<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\UserModel;
use App\Services\PdoService;
use App\Services\SystemConfigService;

final class LegacyApiController
{
    public function faceLogin(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $body = $request->allBody();
        $inputDescriptor = $body['descriptor'] ?? null;

        if (!is_array($inputDescriptor) || count($inputDescriptor) !== 128) {
            Response::json(['success' => false, 'message' => 'Descriptor facial inválido.'], 400);
        }

        $cfg = (new SystemConfigService())->get();
        $threshold = is_numeric($cfg['face_threshold'] ?? null) ? (float) $cfg['face_threshold'] : 0.40;

        $pdo = PdoService::make();
        $users = (new UserModel($pdo))->obtenerTodosConDescriptorVerificado();

        if ($users === []) {
            Response::json(['success' => false, 'message' => 'No hay usuarios con reconocimiento facial registrado.']);
        }

        $bestMatch = null;
        $bestDist = PHP_FLOAT_MAX;

        foreach ($users as $user) {
            $stored = json_decode((string) ($user['face_descriptor'] ?? ''), true);
            if (!is_array($stored) || count($stored) !== 128) {
                continue;
            }

            $sum = 0.0;
            for ($i = 0; $i < 128; $i++) {
                $diff = (float) $inputDescriptor[$i] - (float) $stored[$i];
                $sum += $diff * $diff;
            }
            $dist = sqrt($sum);

            if ($dist < $bestDist) {
                $bestDist = $dist;
                $bestMatch = $user;
            }
        }

        if (!$bestMatch || $bestDist >= $threshold) {
            Response::json([
                'success' => false,
                'message' => 'Rostro no reconocido. Verifica la iluminación o intenta con contraseña.',
                'distance' => $bestDist < PHP_FLOAT_MAX ? round($bestDist, 4) : null,
                'threshold' => round($threshold, 2),
            ]);
        }

        $userId = (int) $bestMatch['id'];
        $email = (string) ($bestMatch['email'] ?? '');

        $otpMin = is_numeric($cfg['otp_expiracion_min'] ?? null) ? (int) $cfg['otp_expiracion_min'] : 5;
        $otpMin = max(1, min(60, $otpMin));

        $otp = random_int(100000, 999999);
        $expiracion = date('Y-m-d H:i:s', strtotime('+' . $otpMin . ' minutes'));

        require_once __DIR__ . '/../../config/mailer.php';

        $model = new UserModel($pdo);
        $model->actualizarOTP($userId, $otp, $expiracion);

        $_SESSION['temp_user_id'] = $userId;
        $_SESSION['otp_tipo'] = 'login';
        $_SESSION['temp_email'] = $email;

        enviarOTP($email, $otp, 'login');

        Response::json([
            'success' => true,
            'redirect' => $request->basePath() . '/otp',
            'distance' => round($bestDist, 4),
            'email' => $email,
        ]);
    }
}
