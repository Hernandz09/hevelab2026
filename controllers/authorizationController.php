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




    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $usuario = trim($_POST['usuario']);
            $password = $_POST['password'];
            $user = $this->userModel->buscarUsuario($usuario);

            if ($user && password_verify($password, $user['password'])) {
                $otp = random_int(100000, 999999);
                $expiracion = date("Y-m-d H:i:s", strtotime('+5 minutes'));
                $this->userModel->actualizarOTP($user['id'], $otp, $expiracion);

                $_SESSION['temp_user_id'] = $user['id'];
                $_SESSION['temp_email'] = $user['email'];
                $_SESSION['otp_tipo'] = 'login';

                $emailEnviado = enviarOTP($user['email'], $otp, 'login');

                if (!$emailEnviado) {
                    echo "<script>
                        alert('No se pudo enviar el correo. Tu código temporal es: $otp');
                        window.location.href = '../validater/verificar_otp.php';
                    </script>";
                }
                else {
                    header('Location: ../validater/verificar_otp.php');
                }
                exit;
            }
            else {
                return "Usuario o contraseña incorrectos.";
            }
        }
    }




    public function registro()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $user_id = (int)($_POST['user_id_hidden'] ?? 0);
        $nombre_completo = trim($_POST['nombre_completo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $face_descriptor = $_POST['face_descriptor'] ?? null;

        if (empty($nombre_completo))
            return ['error' => 'El nombre es obligatorio.'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return ['error' => 'Email no válido.'];

        if (!empty($face_descriptor)) {
            $password = 'FacePass_' . bin2hex(random_bytes(7));
            $confirm_password = $password;
        }

        if ($password !== $confirm_password)
            return ['error' => 'Contraseñas no coinciden.'];
        if (strlen($password) < 8)
            return ['error' => 'Contraseña muy corta.'];

        $otp = random_int(100000, 999999);
        $expiracion = date("Y-m-d H:i:s", strtotime('+5 minutes'));
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        if ($user_id > 0) {
            $stmt = $this->pdo->prepare("UPDATE usuarios SET usuario = ?, password = ?, otp_code = ?, otp_expiracion = ?, registro_etapa = 'Verificación OTP' WHERE id = ?");
            $stmt->execute([$nombre_completo, $password_hash, $otp, $expiracion, $user_id]);
        }
        else {
            $user_id = $this->userModel->registrarUsuario($nombre_completo, $email, $password_hash, 'Verificación OTP');
            $this->userModel->actualizarOTP($user_id, $otp, $expiracion);
        }

        $_SESSION['temp_user_id'] = $user_id;
        $_SESSION['otp_tipo'] = 'registro';
        $_SESSION['temp_email'] = $email;
        $_SESSION['temp_descriptor'] = $face_descriptor;

        enviarOTP($email, $otp, 'registro');
        header('Location: ../validater/verificar_otp.php?sent=1');
        exit;
    }




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

            return ['info' => 'Si el correo está registrado, recibirás un código en breve.'];
        }

        $otp = random_int(100000, 999999);
        $expiracion = date("Y-m-d H:i:s", strtotime('+10 minutes'));

        $this->userModel->actualizarOTP($user['id'], $otp, $expiracion);

        $_SESSION['otp_tipo'] = 'reset';
        $_SESSION['temp_reset_id'] = $user['id'];
        $_SESSION['temp_email'] = $email;

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




    public function resetPassword()
    {
        if (!isset($_SESSION['reset_user_id'])) {
            header('Location: ../login/index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $password = $_POST['password'] ?? '';
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




    public function verificarOTP()
    {
        $tipo = $_SESSION['otp_tipo'] ?? 'login';


        if (isset($_GET['reenviar']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->reenviarOTP($tipo);
            header('Location: verificar_otp.php?reenviado=1');
            exit;
        }


        if ($tipo === 'registro') {
            if (!isset($_SESSION['temp_user_id'])) {
                header('Location: ../register/registrar.php');
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $otp_ingresado = trim($_POST['otp']);
                $user_id = $_SESSION['temp_user_id'];
                $db_user = $this->userModel->validarOTP($user_id, $otp_ingresado);

                if ($db_user) {
                    $this->userModel->marcarVerificado($user_id);
                    $this->userModel->limpiarOTP($user_id);
                    $this->userModel->actualizarEtapaRegistro($user_id, 'Completado');

                    $fd = $_SESSION['temp_descriptor'] ?? null;
                    if ($fd) {
                        $descriptor = json_decode($fd, true);
                        if (is_array($descriptor) && count($descriptor) === 128) {
                            $this->userModel->guardarDescriptorFacial($user_id, $descriptor);
                        }
                    }

                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $db_user['usuario'];
                    unset($_SESSION['temp_user_id'], $_SESSION['otp_tipo'], $_SESSION['temp_email'], $_SESSION['temp_descriptor']);
                    header('Location: ../../Dashboard.php');
                    exit;
                }
                else {
                    return 'Código inválido o expirado.';
                }
            }
        }


        elseif ($tipo === 'reset') {
            if (!isset($_SESSION['temp_reset_id'])) {
                header('Location: ../forgot/solicitar.php');
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $otp_ingresado = trim($_POST['otp']);
                $id = $_SESSION['temp_reset_id'];
                $user = $this->userModel->validarOTP($id, $otp_ingresado);

                if ($user) {

                    $_SESSION['reset_user_id'] = $id;
                    unset($_SESSION['temp_reset_id'], $_SESSION['otp_tipo'], $_SESSION['temp_email']);
                    header('Location: ../forgot/nueva_password.php');
                    exit;
                }
                else {
                    return 'Código inválido o expirado.';
                }
            }
        }


        else {
            if (!isset($_SESSION['temp_user_id'])) {
                header('Location: ../login/index.php');
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $otp_ingresado = trim($_POST['otp']);
                $id = $_SESSION['temp_user_id'];
                $user = $this->userModel->validarOTP($id, $otp_ingresado);

                if ($user) {
                    $this->userModel->limpiarOTP($id);
                    $this->userModel->registrarLogin($id);
                    $_SESSION['user_id'] = $id;
                    unset($_SESSION['otp_tipo'], $_SESSION['temp_email']);
                    header('Location: ../../Dashboard.php');
                    exit;
                }
                else {
                    return 'Código inválido o expirado.';
                }
            }
        }
    }



    private function reenviarOTP(string $tipo): void
    {
        $otp = random_int(100000, 999999);

        if ($tipo === 'registro' && isset($_SESSION['temp_user_id'])) {
            $exp = date("Y-m-d H:i:s", strtotime('+5 minutes'));
            $this->userModel->actualizarOTP($_SESSION['temp_user_id'], $otp, $exp);
            enviarOTP($_SESSION['temp_email'], $otp, 'registro');

        }
        elseif ($tipo === 'reset' && isset($_SESSION['temp_reset_id'])) {
            $exp = date("Y-m-d H:i:s", strtotime('+10 minutes'));
            $this->userModel->actualizarOTP($_SESSION['temp_reset_id'], $otp, $exp);
            enviarOTP($_SESSION['temp_email'], $otp, 'reset');

        }
        elseif ($tipo === 'login' && isset($_SESSION['temp_user_id'])) {
            $exp = date("Y-m-d H:i:s", strtotime('+5 minutes'));
            $this->userModel->actualizarOTP($_SESSION['temp_user_id'], $otp, $exp);
            enviarOTP($_SESSION['temp_email'], $otp, 'login');
        }
    }
}
?>
