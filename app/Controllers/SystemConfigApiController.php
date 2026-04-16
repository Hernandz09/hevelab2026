<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\SystemConfigService;

final class SystemConfigApiController
{
    public function read(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            Response::json(['success' => false, 'message' => 'No autorizado.'], 401);
        }

        $cfg = (new SystemConfigService())->get();
        Response::json(['success' => true, 'config' => $cfg]);
    }

    public function save(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            Response::json(['success' => false, 'message' => 'No autorizado.'], 401);
        }

        $body = $request->allBody();
        if (!is_array($body)) {
            Response::json(['success' => false, 'message' => 'Cuerpo JSON inválido.'], 400);
        }

        $cfg = (new SystemConfigService())->get();
        if (isset($body['face_threshold'])) {
            $t = (float) $body['face_threshold'];
            if ($t < 0.10 || $t > 1.0) {
                Response::json(['success' => false, 'message' => 'El umbral debe estar entre 0.10 y 1.00.'], 400);
            }
            $cfg['face_threshold'] = round($t, 2);
        }
        if (isset($body['otp_expiracion_min'])) {
            $otp = (int) $body['otp_expiracion_min'];
            if ($otp < 1 || $otp > 60) {
                Response::json(['success' => false, 'message' => 'La expiración OTP debe estar entre 1 y 60 minutos.'], 400);
            }
            $cfg['otp_expiracion_min'] = $otp;
        }
        if (isset($body['max_intentos_login'])) {
            $max = (int) $body['max_intentos_login'];
            if ($max < 1 || $max > 20) {
                Response::json(['success' => false, 'message' => 'Los intentos máximos deben estar entre 1 y 20.'], 400);
            }
            $cfg['max_intentos_login'] = $max;
        }

        $cfg['updated_at'] = date('Y-m-d H:i:s');
        $cfg['updated_by'] = (int) ($_SESSION['user_id'] ?? 0);

        $configPath = dirname(__DIR__, 2) . '/config/system_config.json';
        $saved = file_put_contents($configPath, json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($saved === false) {
            Response::json(['success' => false, 'message' => 'Error al guardar configuración.'], 500);
        }

        Response::json(['success' => true, 'message' => 'Configuración guardada correctamente.', 'config' => $cfg]);
    }
}
