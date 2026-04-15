<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

$action = $_GET['action'] ?? 'stats';

function limpiarNoVerificados($pdo): int
{




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


    case 'stats':

        $eliminados = limpiarNoVerificados($pdo);


        $total = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE verificado = 1")->fetchColumn();


        $conFacial = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE face_descriptor IS NOT NULL AND verificado = 1")->fetchColumn();


        $sinFacial = $total - $conFacial;


        $pendientes = $pdo->query("
            SELECT COUNT(*) FROM usuarios
            WHERE verificado = 0
              AND otp_expiracion >= NOW()
        ")->fetchColumn();


        $registrosPorDia = $pdo->query("
            SELECT DATE(created_at) as dia, COUNT(*) as total
            FROM usuarios
            WHERE verificado = 1
              AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY dia ASC
        ")->fetchAll(PDO::FETCH_ASSOC);


        $registros30 = $pdo->query("
            SELECT DATE(created_at) as dia, COUNT(*) as total
            FROM usuarios
            WHERE verificado = 1
              AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY dia ASC
        ")->fetchAll(PDO::FETCH_ASSOC);


        $hoy = $pdo->query("
            SELECT COUNT(*) FROM usuarios
            WHERE verificado = 1 AND DATE(created_at) = CURDATE()
        ")->fetchColumn();


        $semana = $pdo->query("
            SELECT COUNT(*) FROM usuarios
            WHERE verificado = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ")->fetchColumn();

        echo json_encode([
            'total' => (int)$total,
            'conFacial' => (int)$conFacial,
            'sinFacial' => (int)$sinFacial,
            'pendientes' => (int)$pendientes,
            'hoy' => (int)$hoy,
            'semana' => (int)$semana,
            'registrosPorDia' => $registrosPorDia,
            'registros30' => $registros30,
            'eliminados' => $eliminados,
        ]);
        break;


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
                intentos_login,
                registro_etapa
            FROM usuarios
            ORDER BY created_at DESC
            LIMIT 100
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['usuarios' => $usuarios]);
        break;


    case 'cleanup':
        $eliminados = limpiarNoVerificados($pdo);
        echo json_encode([
            'success' => true,
            'eliminados' => $eliminados,
            'mensaje' => $eliminados > 0
            ? "Se eliminaron $eliminados usuarios no verificados."
            : "No hay usuarios pendientes de eliminar.",
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no reconocida']);
}
