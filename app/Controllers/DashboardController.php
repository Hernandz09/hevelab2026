<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\PdoService;

final class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $session = $this->requireSession($request);

        $content = $this->view('layouts.app', [
            'title' => 'Dashboard',
            'initialPage' => 'dashboard',
            'userName' => $session['userName'],
            'userEmail' => $session['userEmail'],
        ]);

        Response::html($content);
    }

    public function overview(Request $request): void
    {
        $this->requireSession($request);
        $pdo = PdoService::make();
        $now = date('Y-m-d H:i:s');

        $total = (int) ($pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() ?: 0);
        $week = (int) ($pdo->query('SELECT COUNT(*) FROM usuarios WHERE created_at >= (CURDATE() - INTERVAL 7 DAY)')->fetchColumn() ?: 0);
        $facial = (int) ($pdo->query('SELECT COUNT(*) FROM usuarios WHERE face_descriptor IS NOT NULL')->fetchColumn() ?: 0);
        $todayVerified = (int) ($pdo->query('SELECT COUNT(*) FROM usuarios WHERE verificado = 1 AND DATE(created_at) = CURDATE()')->fetchColumn() ?: 0);
        $pendingStmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE verificado = 0 AND otp_code IS NOT NULL AND otp_expiracion IS NOT NULL AND otp_expiracion > ?');
        $pendingStmt->execute([$now]);
        $pendingOtp = (int) ($pendingStmt->fetchColumn() ?: 0);

        $seriesStmt = $pdo->query("
            SELECT DATE(created_at) AS dia, COUNT(*) AS total
            FROM usuarios
            WHERE verificado = 1 AND created_at >= (CURDATE() - INTERVAL 29 DAY)
            GROUP BY DATE(created_at)
            ORDER BY dia ASC
        ");
        $seriesRows = $seriesStmt->fetchAll();
        $series = [];
        $map = [];
        foreach ($seriesRows as $r) {
            $map[(string) ($r['dia'] ?? '')] = (int) ($r['total'] ?? 0);
        }
        for ($i = 29; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} day"));
            $series[] = ['date' => $day, 'count' => (int) ($map[$day] ?? 0)];
        }

        $verifiedFace = (int) ($pdo->query('SELECT COUNT(*) FROM usuarios WHERE verificado = 1 AND face_descriptor IS NOT NULL')->fetchColumn() ?: 0);
        $verifiedPass = (int) ($pdo->query('SELECT COUNT(*) FROM usuarios WHERE verificado = 1 AND face_descriptor IS NULL')->fetchColumn() ?: 0);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $deletedSession = (int) ($_SESSION['dashboard_deleted'] ?? 0);

        Response::json([
            'success' => true,
            'stats' => [
                'total' => $total,
                'week' => $week,
                'facial' => $facial,
                'todayVerified' => $todayVerified,
                'pendingOtp' => $pendingOtp,
                'deletedSession' => $deletedSession,
            ],
            'series' => $series,
            'auth' => [
                'face' => $verifiedFace,
                'password' => $verifiedPass,
            ],
        ]);
    }

    public function cleanupExpiredOtp(Request $request): void
    {
        $this->requireSession($request);
        $pdo = PdoService::make();
        $now = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE verificado = 0 AND otp_expiracion IS NOT NULL AND otp_expiracion <= ?');
        $stmt->execute([$now]);
        $deleted = (int) $stmt->rowCount();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['dashboard_deleted'] = (int) ($_SESSION['dashboard_deleted'] ?? 0) + $deleted;

        Response::json(['success' => true, 'deleted' => $deleted, 'deletedSession' => (int) $_SESSION['dashboard_deleted']]);
    }

    /**
     * @return array{userName:string,userEmail:string}
     */
    private function requireSession(Request $request): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            Response::redirect($request->basePath() . '/login');
        }

        $pdo = PdoService::make();
        $stmt = $pdo->prepare('SELECT usuario, email FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return [
            'userName' => (string) ($row['usuario'] ?? 'Admin'),
            'userEmail' => (string) ($row['email'] ?? ''),
        ];
    }
}
