<?php
/**
 * api/dashboard_data.php
 * API REST para el Dashboard de VIISION ERP
 * Provee: métricas, usuarios, y limpieza automática de no-verificados.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

// Solo admin autenticado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

$action = $_GET['action'] ?? 'stats';

// ══════════════════════════════════════════════════════
//  LIMPIEZA AUTOMÁTICA: Eliminar usuarios no verificados
//  con OTP expirado hace más de 5 minutos
// ══════════════════════════════════════════════════════
function limpiarNoVerificados($pdo): int
{
    // Elimina usuarios que:
    // 1. No están verificados (verificado = 0)
    // 2. Tienen OTP expirado hace más de 5 minutos
    // 3. SU OTP no es nulo (es decir, pasaron por registro pero nunca validaron)
    $stmt = $pdo->prepare("
        DELETE FROM usuarios
        WHERE verificado = 0
          AND otp_expiracion IS NOT NULL
          AND otp_expiracion < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $stmt->execute();
    return $stmt->rowCount();
}

switch ($action) {

    // ── Estadísticas generales ──────────────────────────
    case 'stats':
        // Limpiar no-verificados antes de contar
        $eliminados = limpiarNoVerificados($pdo);

        // Total usuarios verificados
        $total = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE verificado = 1")->fetchColumn();

        // Usuarios con facial activado
        $conFacial = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE face_descriptor IS NOT NULL AND verificado = 1")->fetchColumn();

        // Usuarios sin facial
        $sinFacial = $total - $conFacial;

        // Pendientes de verificar OTP (registrados pero aún no validados, OTP aún vigente)
        $pendientes = $pdo->query("
            SELECT COUNT(*) FROM usuarios
            WHERE verificado = 0
              AND otp_expiracion >= NOW()
        ")->fetchColumn();

        // Últimos 7 días: registros por día
        $registrosPorDia = $pdo->query("
            SELECT DATE(created_at) as dia, COUNT(*) as total
            FROM usuarios
            WHERE verificado = 1
              AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY dia ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Últimos 30 días de registros para gráfica de área
        $registros30 = $pdo->query("
            SELECT DATE(created_at) as dia, COUNT(*) as total
            FROM usuarios
            WHERE verificado = 1
              AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY dia ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Usuarios verificados hoy
        $hoy = $pdo->query("
            SELECT COUNT(*) FROM usuarios
            WHERE verificado = 1 AND DATE(created_at) = CURDATE()
        ")->fetchColumn();

        // Usuarios verificados esta semana
        $semana = $pdo->query("
            SELECT COUNT(*) FROM usuarios
            WHERE verificado = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ")->fetchColumn();

        echo json_encode([
            'total'           => (int)$total,
            'conFacial'       => (int)$conFacial,
            'sinFacial'       => (int)$sinFacial,
            'pendientes'      => (int)$pendientes,
            'hoy'             => (int)$hoy,
            'semana'          => (int)$semana,
            'registrosPorDia' => $registrosPorDia,
            'registros30'     => $registros30,
            'eliminados'      => $eliminados, // Cuántos fueron limpiados en esta llamada
        ]);
        break;

    // ── Lista de usuarios ───────────────────────────────
    case 'usuarios':
        limpiarNoVerificados($pdo);

        $usuarios = $pdo->query("
            SELECT
                id,
                usuario,
                email,
                verificado,
                CASE WHEN face_descriptor IS NOT NULL THEN 1 ELSE 0 END AS tiene_facial,
                created_at,
                ultimo_login,
                intentos_login
            FROM usuarios
            ORDER BY created_at DESC
            LIMIT 100
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['usuarios' => $usuarios]);
        break;

    // ── Limpieza manual forzada ─────────────────────────
    case 'cleanup':
        $eliminados = limpiarNoVerificados($pdo);
        echo json_encode([
            'success'    => true,
            'eliminados' => $eliminados,
            'mensaje'    => $eliminados > 0
                ? "Se eliminaron $eliminados usuarios no verificados."
                : "No hay usuarios pendientes de eliminar.",
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no reconocida']);
}
