<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

final class HomeController extends Controller
{
    public function index(Request $request): void
    {
        $content = $this->view('layouts.app', [
            'title' => 'Inicio',
            'initialPage' => 'home',
        ]);

        Response::html($content);
    }

    public function products(Request $request): void
    {
        $content = $this->view('layouts.app', [
            'title' => 'Productos',
            'initialPage' => 'products',
        ]);

        Response::html($content);
    }
}
