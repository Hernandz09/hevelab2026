<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\PdoService;

final class AdminController extends Controller
{
    public function users(Request $request): void
    {
        $session = $this->requireSession($request);
        $html = $this->view('layouts.app', [
            'title' => 'Usuarios',
            'initialPage' => 'users',
            'userName' => $session['userName'],
            'userEmail' => $session['userEmail'],
        ]);
        Response::html($html);
    }

    public function settings(Request $request): void
    {
        $session = $this->requireSession($request);
        $html = $this->view('layouts.app', [
            'title' => 'Configuración',
            'initialPage' => 'settings',
            'userName' => $session['userName'],
            'userEmail' => $session['userEmail'],
        ]);
        Response::html($html);
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
