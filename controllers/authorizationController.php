<?php
require_once __DIR__ . '/../Models/userModel.php';
require_once __DIR__ . '/../config/mailer.php';

class AuthorizationController
{
    private $userModel;

    public function __construct($pdo)
    {
        $this->userModel = new UserModel($pdo);
    }

    // ──────────────────────────────────────────────
    //  LOGIN
    // ──────────────────────────────────────────────
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $usuario  = trim($_POST['usuario']);
            $password = $_POST['password'];
            $user     = $this->userModel->buscarUsuario($usuario);

            if ($user && password_verify($password, $user['password'])) {
                $otp        = random_int(100000, 999999);
                $expiracion = date("Y-m-d H:i:s", strtotime('+5 minutes'));
                $this->userModel->actualizarOTP($user['id'], $otp, $expiracion);

                $_SESSION['temp_user_id'] = $user['id'];
                $_SESSION['temp_email']   = $user['email'];
                $_SESSION['otp_tipo']     = 'login';

                $emailEnviado = enviarOTP($user['email'], $otp, 'login');

                if (!$emailEnviado) {
                    echo "<script>
                        alert('No se pudo enviar el correo. Tu código temporal es: $otp');
                        window.location.href = '../validater/verificar_otp.php';
                    </script>";
                } else {
                    header('Location: ../validater/verificar_otp.php');
                }
                exit;
            } else {
                return "Usuario o contraseña incorrectos.";
            }
        }
    }

    // ──────────────────────────────────────────────
    //  REGISTRO (solo email + password → OTP)
    // ──────────────────────────────────────────────
    public function registro()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $nombre_completo  = trim($_POST['nombre_completo'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $face_descriptor  = $_POST['face_descriptor'] ?? null;

        // Validar nombre completo
        if (empty($nombre_completo)) {
            return ['error' => 'El nombre completo es obligatorio.'];
        }
        if (strlen($nombre_completo) < 3 || strlen($nombre_completo) > 100) {
            return ['error' => 'El nombre debe tener entre 3 y 100 caracteres.'];
        }

        // Si mandó reconocimiento facial, ignoramos la clave manual y generamos una segura
        if (!empty($face_descriptor)) {
            $password = 'FacePass_' . bin2hex(random_bytes(7));
            $confirm_password = $password;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'El correo electrónico no es válido.'];
        }
        if ($password !== $confirm_password) {
            return ['error' => 'Las contraseñas no coinciden.'];
        }
        if (strlen($password) < 8 || strlen($password) > 30) {
            return ['error' => 'La contraseña debe tener entre 8 y 30 caracteres.'];
        }
        if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return ['error' => 'La contraseña debe contener letras y números.'];
        }
        if ($this->userModel->buscarPorEmail($email)) {
            return ['error' => 'Este correo ya está registrado.'];
        }

        $otp           = random_int(100000, 999999);
        $expiracion    = date("Y-m-d H:i:s", strtotime('+5 minutes'));
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $_SESSION['temp_registro'] = [
            'nombre_completo' => $nombre_completo,
            'email'           => $email,
            'password_hash'   => $password_hash,
            'otp'             => $otp,
            'otp_expiracion'  => $expiracion,
            'face_descriptor' => $_POST['face_descriptor'] ?? null,
        ];
        $_SESSION['otp_tipo']   = 'registro';
        $_SESSION['temp_email'] = $email;

        $emailEnviado = enviarOTP($email, $otp, 'registro');
        if (!$emailEnviado) {
            echo "<script>
                alert('No se pudo enviar el correo. Tu código temporal es: $otp');
                window.location.href = '../validater/verificar_otp.php?sent=1';
            </script>";
        } else {
            header('Location: ../validater/verificar_otp.php?sent=1');
        }
        exit;
    }

    // ──────────────────────────────────────────────
    //  RECUPERAR CONTRASEÑA — paso 1: pedir email
    // ──────────────────────────────────────────────
    public function solicitarReset()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Ingresa un correo electrónico válido.'];
        }

        $user = $this->userModel->buscarPorEmail($email);

        if (!$user) {
            // Mensaje genérico por seguridad (no revela si el email existe)
            return ['info' => 'Si el correo está registrado, recibirás un código en breve.'];
        }

        $otp        = random_int(100000, 999999);
        $expiracion = date("Y-m-d H:i:s", strtotime('+10 minutes')); // 10 min para reset

        $this->userModel->actualizarOTP($user['id'], $otp, $expiracion);

        $_SESSION['otp_tipo']      = 'reset';
        $_SESSION['temp_reset_id'] = $user['id'];
        $_SESSION['temp_email']    = $email;

        $emailEnviado = enviarOTP($email, $otp, 'reset');

        if (!$emailEnviado) {
            echo "<script>
                alert('No se pudo enviar el correo. Tu código temporal es: $otp');
                window.location.href = '../validater/verificar_otp.php';
            </script>";
            exit;
        }

        header('Location: ../validater/verificar_otp.php');
        exit;
    }

    // ──────────────────────────────────────────────
    //  RECUPERAR CONTRASEÑA — paso 3: nueva clave
    // ──────────────────────────────────────────────
    public function resetPassword()
    {
        if (!isset($_SESSION['reset_user_id'])) {
            header('Location: ../login/index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $password         = $_POST['password']         ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm_password) {
            return ['error' => 'Las contraseñas no coinciden.'];
        }
        if (strlen($password) < 8 || strlen($password) > 30) {
            return ['error' => 'La contraseña debe tener entre 8 y 30 caracteres.'];
        }
        if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return ['error' => 'La contraseña debe contener letras y números.'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->userModel->actualizarPassword($_SESSION['reset_user_id'], $hash);
        $this->userModel->limpiarOTP($_SESSION['reset_user_id']);

        $userId = $_SESSION['reset_user_id'];
        unset($_SESSION['reset_user_id'], $_SESSION['otp_tipo']);

        header('Location: ../login/index.php?reset=ok');
        exit;
    }

    // ──────────────────────────────────────────────
    //  VERIFICAR OTP  (login | registro | reset)
    // ──────────────────────────────────────────────
    public function verificarOTP()
    {
        $tipo = $_SESSION['otp_tipo'] ?? 'login';

        // ── Manejar petición de reenvío ──────────
        if (isset($_GET['reenviar']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->reenviarOTP($tipo);
            header('Location: verificar_otp.php?reenviado=1');
            exit;
        }

        // ── OTP de REGISTRO ──────────────────────
        if ($tipo === 'registro') {
            if (!isset($_SESSION['temp_registro'])) {
                header('Location: ../register/registrar.php');
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $otp_ingresado = trim($_POST['otp']);
                $registro      = $_SESSION['temp_registro'];
                $otpValido     = ($otp_ingresado == $registro['otp']);
                $noExpirado    = (strtotime($registro['otp_expiracion']) > time());

                if ($otpValido && $noExpirado) {
                    try {
                        $email           = $registro['email'];
                        $phash           = $registro['password_hash'];
                        $nombre_completo = $registro['nombre_completo'] ?? $email;

                        if ($this->userModel->registrarUsuario($nombre_completo, $email, $phash)) {
                            $user = $this->userModel->buscarUsuario($nombre_completo);
                            // Fallback: buscar por email si no encontró por nombre
                            if (!$user) {
                                $user = $this->userModel->buscarPorEmailCompleto($email);
                            }
                            $_SESSION['user_id']   = $user['id'];
                            $_SESSION['user_name'] = $nombre_completo;

                            // Marcar como verificado y registrar ultimo_login
                            $this->userModel->marcarVerificado($user['id']);

                            // Guardar descriptor facial si fue proporcionado
                            $fd = $registro['face_descriptor'] ?? null;
                            if ($fd) {
                                $descriptor = json_decode($fd, true);
                                if (is_array($descriptor) && count($descriptor) === 128) {
                                    $this->userModel->guardarDescriptorFacial($user['id'], $descriptor);
                                }
                            }

                            unset($_SESSION['temp_registro'], $_SESSION['otp_tipo'], $_SESSION['temp_email']);
                            header('Location: ../../Dashboard.php');
                            exit;
                        }
                    } catch (PDOException $e) {
                        return 'Error al crear la cuenta: ' . $e->getMessage();
                    }
                } else {
                    return 'Código inválido o expirado.';
                }
            }
        }

        // ── OTP de RESET de contraseña ───────────
        elseif ($tipo === 'reset') {
            if (!isset($_SESSION['temp_reset_id'])) {
                header('Location: ../forgot/solicitar.php');
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $otp_ingresado = trim($_POST['otp']);
                $id            = $_SESSION['temp_reset_id'];
                $user          = $this->userModel->validarOTP($id, $otp_ingresado);

                if ($user) {
                    // No limpiamos OTP aquí aún; lo limpiará resetPassword()
                    $_SESSION['reset_user_id'] = $id;
                    unset($_SESSION['temp_reset_id'], $_SESSION['otp_tipo'], $_SESSION['temp_email']);
                    header('Location: ../forgot/nueva_password.php');
                    exit;
                } else {
                    return 'Código inválido o expirado.';
                }
            }
        }

        // ── OTP de LOGIN ────────────────────────
        else {
            if (!isset($_SESSION['temp_user_id'])) {
                header('Location: ../login/index.php');
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $otp_ingresado = trim($_POST['otp']);
                $id            = $_SESSION['temp_user_id'];
                $user          = $this->userModel->validarOTP($id, $otp_ingresado);

                if ($user) {
                    $this->userModel->limpiarOTP($id);
                    $this->userModel->registrarLogin($id);
                    $_SESSION['user_id'] = $id;
                    unset($_SESSION['otp_tipo'], $_SESSION['temp_email']);
                    header('Location: ../../Dashboard.php');
                    exit;
                } else {
                    return 'Código inválido o expirado.';
                }
            }
        }
    }
    // ──────────────────────────────────────────────
    //  REENVIAR OTP (método privado)
    // ──────────────────────────────────────────────
    private function reenviarOTP(string $tipo): void
    {
        $otp = random_int(100000, 999999);

        if ($tipo === 'registro' && isset($_SESSION['temp_registro'])) {
            $exp = date("Y-m-d H:i:s", strtotime('+5 minutes'));
            $_SESSION['temp_registro']['otp']            = $otp;
            $_SESSION['temp_registro']['otp_expiracion'] = $exp;
            enviarOTP($_SESSION['temp_email'], $otp, 'registro');

        } elseif ($tipo === 'reset' && isset($_SESSION['temp_reset_id'])) {
            $exp = date("Y-m-d H:i:s", strtotime('+10 minutes'));
            $this->userModel->actualizarOTP($_SESSION['temp_reset_id'], $otp, $exp);
            enviarOTP($_SESSION['temp_email'], $otp, 'reset');

        } elseif ($tipo === 'login' && isset($_SESSION['temp_user_id'])) {
            $exp = date("Y-m-d H:i:s", strtotime('+5 minutes'));
            $this->userModel->actualizarOTP($_SESSION['temp_user_id'], $otp, $exp);
            enviarOTP($_SESSION['temp_email'], $otp, 'login');
        }
    }
}
?>
