<?php
class UserModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function buscarUsuario($usuario)
    {
        // Busca por nombre de usuario O por email (para login con email)
        $stmt = $this->pdo->prepare("SELECT id, password, email, usuario FROM usuarios WHERE usuario = ? OR email = ? LIMIT 1");
        $stmt->execute([$usuario, $usuario]);
        return $stmt->fetch();
    }

    public function buscarPorEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function buscarPorEmailCompleto($email)
    {
        $stmt = $this->pdo->prepare("SELECT id, password, email, usuario FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function actualizarOTP($id, $otp, $expiracion)
    {
        $update = $this->pdo->prepare("UPDATE usuarios SET otp_code = ?, otp_expiracion = ? WHERE id = ?");
        return $update->execute([$otp, $expiracion, $id]);
    }

    public function registrarUsuario($usuario, $email, $password_hash)
    {
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (usuario, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$usuario, $email, $password_hash]);
    }

    public function validarOTP($id, $otp)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND otp_code = ? AND otp_expiracion > NOW()");
        $stmt->execute([$id, $otp]);
        return $stmt->fetch();
    }

    public function limpiarOTP($id)
    {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET otp_code = NULL, otp_expiracion = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function actualizarPassword($id, $hash)
    {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public function guardarDescriptorFacial($id, array $descriptor)
    {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET face_descriptor = ? WHERE id = ?");
        return $stmt->execute([json_encode($descriptor), $id]);
    }

    public function obtenerTodosConDescriptor()
    {
        $stmt = $this->pdo->query("SELECT id, email, face_descriptor FROM usuarios WHERE face_descriptor IS NOT NULL");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarVerificado($id)
    {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET verificado = 1, ultimo_login = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function registrarLogin($id)
    {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET ultimo_login = NOW(), verificado = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function eliminarNoVerificadosExpirados()
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM usuarios
            WHERE verificado = 0
              AND otp_expiracion IS NOT NULL
              AND otp_expiracion < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
?>