<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ProductModel;

final class ApiController extends Controller
{
    private const ALLOWED_PAGES = ['home', 'dashboard', 'products', 'users', 'settings'];

    public function page(Request $request, string $page): void
    {
        if (!in_array($page, self::ALLOWED_PAGES, true)) {
            Response::notFound('Page not found');
        }

        $html = $this->view('pages.' . $page);
        Response::json([
            'page' => $page,
            'html' => $html,
        ]);
    }

    public function products(Request $request): void
    {
        usleep(150000);
        $products = (new ProductModel())->all();
        Response::json([
            'items' => $products,
            'total' => count($products),
        ]);
    }
}
