<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\UserModel;
use App\Services\PdoService;
use App\Services\SystemConfigService;

final class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->cleanupExpiredOtpUnverified();

        $html = $this->view('pages.auth.login', [
            'error' => $_SESSION['flash_error'] ?? null,
            'info' => $_SESSION['flash_info'] ?? null,
            'resetOk' => ($_GET['reset'] ?? '') === 'ok',
            'basePath' => $request->basePath(),
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_info']);

        Response::html($html);
    }

    public function login(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $usuario = trim((string) ($request->allBody()['usuario'] ?? ''));
        $password = (string) ($request->allBody()['password'] ?? '');

        if ($usuario === '' || $password === '') {
            $_SESSION['flash_error'] = 'Usuario y contraseña son requeridos.';
            Response::redirect($request->basePath() . '/login');
        }

        $pdo = PdoService::make();
        $model = new UserModel($pdo);
        $user = $model->buscarUsuario($usuario);

        if (!$user || !is_string($user['password'] ?? null) || !password_verify($password, (string) $user['password'])) {
            $_SESSION['flash_error'] = 'Usuario o contraseña incorrectos.';
            Response::redirect($request->basePath() . '/login');
        }

        $cfg = (new SystemConfigService())->get();
        $otpMin = is_numeric($cfg['otp_expiracion_min'] ?? null) ? (int) $cfg['otp_expiracion_min'] : 5;
        $otpMin = max(1, min(60, $otpMin));

        $otp = random_int(100000, 999999);
        $expiracion = date('Y-m-d H:i:s', strtotime('+' . $otpMin . ' minutes'));

        $model->actualizarOTP((int) $user['id'], $otp, $expiracion);

        $_SESSION['temp_user_id'] = (int) $user['id'];
        $_SESSION['temp_email'] = (string) ($user['email'] ?? '');
        $_SESSION['otp_tipo'] = 'login';
        $_SESSION['otp_expires_at'] = $expiracion;

        require_once __DIR__ . '/../../config/mailer.php';
        $sent = enviarOTP((string) $_SESSION['temp_email'], $otp, 'login');
        $delivery = (string) ($GLOBALS['hevelab_mail_delivery'] ?? '');
        if (!$sent) {
            $_SESSION['flash_info'] = 'No se pudo enviar el OTP por correo. Verifica la configuración SMTP.';
        } elseif ($delivery === 'log' && in_array((string) ($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1'], true)) {
            $_SESSION['flash_info'] = 'SMTP no configurado. En local, tu OTP es: ' . $otp . ' (también se guardó en storage/logs/otp.log)';
        }

        Response::redirect($request->basePath() . '/otp');
    }

    public function showRegister(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->cleanupExpiredOtpUnverified();

        $html = $this->view('pages.auth.register', [
            'error' => $_SESSION['flash_error'] ?? null,
            'info' => $_SESSION['flash_info'] ?? null,
            'basePath' => $request->basePath(),
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_info']);

        Response::html($html);
    }

    public function register(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $nombre = trim((string) ($request->allBody()['nombre_completo'] ?? ''));
        $email = trim((string) ($request->allBody()['email'] ?? ''));
        $password = (string) ($request->allBody()['password'] ?? '');
        $confirm = (string) ($request->allBody()['confirm_password'] ?? '');
        $faceDescriptorRaw = $request->allBody()['face_descriptor'] ?? null;

        if ($nombre === '') {
            $_SESSION['flash_error'] = 'El nombre es obligatorio.';
            Response::redirect($request->basePath() . '/register');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Email no válido.';
            Response::redirect($request->basePath() . '/register');
        }

        $descriptor = null;
        if (is_string($faceDescriptorRaw) && $faceDescriptorRaw !== '') {
            $decoded = json_decode($faceDescriptorRaw, true);
            if (is_array($decoded) && count($decoded) === 128) {
                $descriptor = $decoded;
                $password = 'FacePass_' . bin2hex(random_bytes(7));
                $confirm = $password;
            }
        }

        if ($password !== $confirm) {
            $_SESSION['flash_error'] = 'Contraseñas no coinciden.';
            Response::redirect($request->basePath() . '/register');
        }
        if (strlen($password) < 8) {
            $_SESSION['flash_error'] = 'Contraseña muy corta.';
            Response::redirect($request->basePath() . '/register');
        }

        $pdo = PdoService::make();
        $dup = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? OR usuario = ?');
        $dup->execute([$email, $nombre]);
        if ($dup->fetch()) {
            $_SESSION['flash_error'] = 'El email o nombre de usuario ya existe.';
            Response::redirect($request->basePath() . '/register');
        }

        $cfg = (new SystemConfigService())->get();
        $otpMin = is_numeric($cfg['otp_expiracion_min'] ?? null) ? (int) $cfg['otp_expiracion_min'] : 5;
        $otpMin = max(1, min(60, $otpMin));

        $otp = random_int(100000, 999999);
        $expiracion = date('Y-m-d H:i:s', strtotime('+' . $otpMin . ' minutes'));
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $model = new UserModel($pdo);
        $userId = $model->registrarUsuario($nombre, $email, $hash, 'Verificación OTP');
        $model->actualizarOTP($userId, $otp, $expiracion);

        $_SESSION['temp_user_id'] = $userId;
        $_SESSION['otp_tipo'] = 'registro';
        $_SESSION['temp_email'] = $email;
        $_SESSION['otp_expires_at'] = $expiracion;
        if ($descriptor !== null) {
            $_SESSION['temp_descriptor'] = json_encode($descriptor, JSON_UNESCAPED_SLASHES);
        }

        require_once __DIR__ . '/../../config/mailer.php';
        $sent = enviarOTP($email, $otp, 'registro');
        $delivery = (string) ($GLOBALS['hevelab_mail_delivery'] ?? '');
        if (!$sent) {
            $_SESSION['flash_info'] = 'No se pudo enviar el OTP por correo. Verifica la configuración SMTP.';
        } elseif ($delivery === 'log' && in_array((string) ($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1'], true)) {
            $_SESSION['flash_info'] = 'SMTP no configurado. En local, tu OTP es: ' . $otp . ' (también se guardó en storage/logs/otp.log)';
        }

        Response::redirect($request->basePath() . '/otp');
    }

    public function showOtp(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->cleanupExpiredOtpUnverified();

        $expiredModal = (bool) ($_SESSION['otp_expired_modal'] ?? false);
        $tipo = $expiredModal ? (string) ($_SESSION['otp_expired_tipo'] ?? 'registro') : (string) ($_SESSION['otp_tipo'] ?? 'login');

        if (isset($_GET['reenviar']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->reenviarOtp($tipo);
            Response::redirect($request->basePath() . '/otp?reenviado=1');
        }

        if (!$expiredModal) {
            if ($tipo === 'reset') {
                if (!isset($_SESSION['temp_reset_id'])) {
                    Response::redirect($request->basePath() . '/forgot');
                }
            } else {
                if (!isset($_SESSION['temp_user_id'])) {
                    Response::redirect($request->basePath() . '/login');
                }
            }
        }

        if (!$expiredModal && $tipo === 'registro') {
            $id = (int) ($_SESSION['temp_user_id'] ?? 0);
            if ($id > 0) {
                $now = date('Y-m-d H:i:s');
                $pdo = PdoService::make();
                $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE id = ? AND verificado = 0 AND otp_expiracion IS NOT NULL AND otp_expiracion <= ? LIMIT 1');
                $stmt->execute([$id, $now]);
                if ($stmt->fetch()) {
                    $del = $pdo->prepare('DELETE FROM usuarios WHERE id = ? AND verificado = 0');
                    $del->execute([$id]);

                    $_SESSION['otp_expired_modal'] = true;
                    $_SESSION['otp_expired_tipo'] = 'registro';
                    $_SESSION['otp_expired_email'] = (string) ($_SESSION['temp_email'] ?? '');
                    unset($_SESSION['temp_user_id'], $_SESSION['temp_email'], $_SESSION['temp_descriptor'], $_SESSION['otp_tipo'], $_SESSION['otp_expires_at']);
                    Response::redirect($request->basePath() . '/otp?expired=1');
                }
            }
        }

        $otpExpiresAt = $expiredModal ? '' : (string) ($_SESSION['otp_expires_at'] ?? '');
        $otpUserId = 0;
        if (!$expiredModal) {
            $otpUserId = $tipo === 'reset' ? (int) ($_SESSION['temp_reset_id'] ?? 0) : (int) ($_SESSION['temp_user_id'] ?? 0);
        }
        $emailMostrado = $expiredModal ? (string) ($_SESSION['otp_expired_email'] ?? '') : (string) ($_SESSION['temp_email'] ?? '');

        $otpRemainingSecs = 0;
        if (!$expiredModal && $otpExpiresAt !== '') {
            $ts = strtotime($otpExpiresAt);
            if (is_int($ts) && $ts > 0) {
                $otpRemainingSecs = max(0, $ts - time());
            }
        }

        $html = $this->view('pages.auth.otp', [
            'error' => $_SESSION['flash_error'] ?? null,
            'info' => $_SESSION['flash_info'] ?? null,
            'tipo' => $tipo,
            'emailMostrado' => $emailMostrado,
            'reenviado' => isset($_GET['reenviado']),
            'otpExpiresAt' => $otpExpiresAt,
            'otpUserId' => $otpUserId,
            'otpRemainingSecs' => $otpRemainingSecs,
            'otpExpiredModal' => $expiredModal,
            'basePath' => $request->basePath(),
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_info'], $_SESSION['otp_expired_modal'], $_SESSION['otp_expired_tipo'], $_SESSION['otp_expired_email']);

        Response::html($html);
    }

    public function verifyOtp(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $otpIngresado = trim((string) ($request->allBody()['otp'] ?? ''));
        if ($otpIngresado === '') {
            $_SESSION['flash_error'] = 'Ingresa el código.';
            Response::redirect($request->basePath() . '/otp');
        }

        $tipo = (string) ($_SESSION['otp_tipo'] ?? 'login');
        $pdo = PdoService::make();
        $model = new UserModel($pdo);

        if ($tipo === 'reset') {
            $id = (int) ($_SESSION['temp_reset_id'] ?? 0);
            if ($id <= 0) {
                Response::redirect($request->basePath() . '/forgot');
            }

            $user = $model->validarOTP($id, $otpIngresado);
            if (!$user) {
                $_SESSION['flash_error'] = 'Código inválido o expirado.';
                Response::redirect($request->basePath() . '/otp');
            }

            $_SESSION['reset_user_id'] = $id;
            unset($_SESSION['temp_reset_id'], $_SESSION['otp_tipo'], $_SESSION['temp_email']);
            Response::redirect($request->basePath() . '/reset-password');
        }

        $id = (int) ($_SESSION['temp_user_id'] ?? 0);
        if ($id <= 0) {
            Response::redirect($request->basePath() . '/login');
        }

        $dbUser = $model->validarOTP($id, $otpIngresado);
        if (!$dbUser) {
            if ($tipo === 'registro') {
                $now = date('Y-m-d H:i:s');
                $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE id = ? AND verificado = 0 AND otp_expiracion IS NOT NULL AND otp_expiracion <= ? LIMIT 1');
                $stmt->execute([$id, $now]);
                if ($stmt->fetch()) {
                    $del = $pdo->prepare('DELETE FROM usuarios WHERE id = ? AND verificado = 0');
                    $del->execute([$id]);

                    $_SESSION['otp_expired_modal'] = true;
                    $_SESSION['otp_expired_tipo'] = 'registro';
                    $_SESSION['otp_expired_email'] = (string) ($_SESSION['temp_email'] ?? '');
                    unset($_SESSION['temp_user_id'], $_SESSION['temp_email'], $_SESSION['temp_descriptor'], $_SESSION['otp_tipo'], $_SESSION['otp_expires_at']);
                    Response::redirect($request->basePath() . '/otp?expired=1');
                }
            }
            $_SESSION['flash_error'] = 'Código inválido o expirado.';
            Response::redirect($request->basePath() . '/otp');
        }

        $model->limpiarOTP($id);

        if ($tipo === 'registro') {
            $model->marcarVerificado($id);
            $model->actualizarEtapaRegistro($id, 'Completado');

            $fd = $_SESSION['temp_descriptor'] ?? null;
            if (is_string($fd) && $fd !== '') {
                $descriptor = json_decode($fd, true);
                if (is_array($descriptor) && count($descriptor) === 128) {
                    $model->guardarDescriptorFacial($id, $descriptor);
                }
            }

            unset($_SESSION['temp_user_id'], $_SESSION['temp_descriptor'], $_SESSION['otp_tipo'], $_SESSION['temp_email']);
        } else {
            $model->registrarLogin($id);
            unset($_SESSION['otp_tipo'], $_SESSION['temp_email']);
        }

        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = (string) ($dbUser['usuario'] ?? 'Usuario');
        $_SESSION['user_email'] = (string) ($dbUser['email'] ?? '');

        Response::redirect($request->basePath() . '/dashboard');
    }

    public function showForgot(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $html = $this->view('pages.auth.forgot', [
            'error' => $_SESSION['flash_error'] ?? null,
            'info' => $_SESSION['flash_info'] ?? null,
            'basePath' => $request->basePath(),
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_info']);

        Response::html($html);
    }

    public function requestReset(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $email = trim((string) ($request->allBody()['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Ingresa un correo electrónico válido.';
            Response::redirect($request->basePath() . '/forgot');
        }

        $pdo = PdoService::make();
        $model = new UserModel($pdo);
        $user = $model->buscarPorEmailCompleto($email);

        $_SESSION['flash_info'] = 'Si el correo está registrado, recibirás un código en breve.';
        if (!$user) {
            Response::redirect($request->basePath() . '/forgot');
        }

        $cfg = (new SystemConfigService())->get();
        $otpMin = is_numeric($cfg['otp_expiracion_min'] ?? null) ? (int) $cfg['otp_expiracion_min'] : 5;
        $otpMin = max(1, min(60, $otpMin));

        $otp = random_int(100000, 999999);
        $expiracion = date('Y-m-d H:i:s', strtotime('+' . $otpMin . ' minutes'));

        $model->actualizarOTP((int) $user['id'], $otp, $expiracion);

        $_SESSION['otp_tipo'] = 'reset';
        $_SESSION['temp_reset_id'] = (int) $user['id'];
        $_SESSION['temp_email'] = $email;
        $_SESSION['otp_expires_at'] = $expiracion;

        require_once __DIR__ . '/../../config/mailer.php';
        $sent = enviarOTP($email, $otp, 'reset');
        $delivery = (string) ($GLOBALS['hevelab_mail_delivery'] ?? '');
        if (!$sent) {
            $_SESSION['flash_info'] = 'No se pudo enviar el OTP por correo. Verifica la configuración SMTP.';
        } elseif ($delivery === 'log' && in_array((string) ($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1'], true)) {
            $_SESSION['flash_info'] = 'SMTP no configurado. En local, tu OTP es: ' . $otp . ' (también se guardó en storage/logs/otp.log)';
        }

        Response::redirect($request->basePath() . '/otp');
    }

    public function showResetPassword(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['reset_user_id'])) {
            Response::redirect($request->basePath() . '/login');
        }

        $html = $this->view('pages.auth.reset_password', [
            'error' => $_SESSION['flash_error'] ?? null,
            'info' => $_SESSION['flash_info'] ?? null,
            'basePath' => $request->basePath(),
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_info']);

        Response::html($html);
    }

    public function resetPassword(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userId = (int) ($_SESSION['reset_user_id'] ?? 0);
        if ($userId <= 0) {
            Response::redirect($request->basePath() . '/login');
        }

        $password = (string) ($request->allBody()['password'] ?? '');
        $confirm = (string) ($request->allBody()['confirm_password'] ?? '');

        if ($password !== $confirm) {
            $_SESSION['flash_error'] = 'Las contraseñas no coinciden.';
            Response::redirect($request->basePath() . '/reset-password');
        }
        if (strlen($password) < 8 || strlen($password) > 30) {
            $_SESSION['flash_error'] = 'La contraseña debe tener entre 8 y 30 caracteres.';
            Response::redirect($request->basePath() . '/reset-password');
        }
        if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $_SESSION['flash_error'] = 'La contraseña debe contener letras y números.';
            Response::redirect($request->basePath() . '/reset-password');
        }

        $pdo = PdoService::make();
        $model = new UserModel($pdo);
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $model->actualizarPassword($userId, $hash);
        $model->limpiarOTP($userId);

        unset($_SESSION['reset_user_id'], $_SESSION['otp_tipo']);

        Response::redirect($request->basePath() . '/login?reset=ok');
    }

    public function logout(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();

        Response::redirect($request->basePath() . '/login');
    }

    private function reenviarOtp(string $tipo): void
    {
        $pdo = PdoService::make();
        $model = new UserModel($pdo);

        $cfg = (new SystemConfigService())->get();
        $otpMin = is_numeric($cfg['otp_expiracion_min'] ?? null) ? (int) $cfg['otp_expiracion_min'] : 5;
        $otpMin = max(1, min(60, $otpMin));

        $otp = random_int(100000, 999999);
        $exp = date('Y-m-d H:i:s', strtotime('+' . $otpMin . ' minutes'));

        require_once __DIR__ . '/../../config/mailer.php';

        if ($tipo === 'registro' && isset($_SESSION['temp_user_id'])) {
            $model->actualizarOTP((int) $_SESSION['temp_user_id'], $otp, $exp);
            $_SESSION['otp_expires_at'] = $exp;
            $sent = enviarOTP((string) ($_SESSION['temp_email'] ?? ''), $otp, 'registro');
            $delivery = (string) ($GLOBALS['hevelab_mail_delivery'] ?? '');
            if (!$sent) {
                $_SESSION['flash_info'] = 'No se pudo reenviar el OTP por correo. Verifica la configuración SMTP.';
            } elseif ($delivery === 'log' && in_array((string) ($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1'], true)) {
                $_SESSION['flash_info'] = 'SMTP no configurado. En local, tu OTP es: ' . $otp . ' (también se guardó en storage/logs/otp.log)';
            }
            return;
        }

        if ($tipo === 'reset' && isset($_SESSION['temp_reset_id'])) {
            $model->actualizarOTP((int) $_SESSION['temp_reset_id'], $otp, $exp);
            $_SESSION['otp_expires_at'] = $exp;
            $sent = enviarOTP((string) ($_SESSION['temp_email'] ?? ''), $otp, 'reset');
            $delivery = (string) ($GLOBALS['hevelab_mail_delivery'] ?? '');
            if (!$sent) {
                $_SESSION['flash_info'] = 'No se pudo reenviar el OTP por correo. Verifica la configuración SMTP.';
            } elseif ($delivery === 'log' && in_array((string) ($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1'], true)) {
                $_SESSION['flash_info'] = 'SMTP no configurado. En local, tu OTP es: ' . $otp . ' (también se guardó en storage/logs/otp.log)';
            }
            return;
        }

        if ($tipo === 'login' && isset($_SESSION['temp_user_id'])) {
            $model->actualizarOTP((int) $_SESSION['temp_user_id'], $otp, $exp);
            $_SESSION['otp_expires_at'] = $exp;
            $sent = enviarOTP((string) ($_SESSION['temp_email'] ?? ''), $otp, 'login');
            $delivery = (string) ($GLOBALS['hevelab_mail_delivery'] ?? '');
            if (!$sent) {
                $_SESSION['flash_info'] = 'No se pudo reenviar el OTP por correo. Verifica la configuración SMTP.';
            } elseif ($delivery === 'log' && in_array((string) ($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1'], true)) {
                $_SESSION['flash_info'] = 'SMTP no configurado. En local, tu OTP es: ' . $otp . ' (también se guardó en storage/logs/otp.log)';
            }
        }
    }

    private function cleanupExpiredOtpUnverified(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $last = $_SESSION['hv_last_otp_cleanup'] ?? 0;
            if (is_int($last) && $last > 0 && (time() - $last) < 30) {
                return;
            }
            $_SESSION['hv_last_otp_cleanup'] = time();
        }

        $pdo = PdoService::make();
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE verificado = 0 AND otp_expiracion IS NOT NULL AND otp_expiracion <= ?');
        $stmt->execute([$now]);
    }
}
