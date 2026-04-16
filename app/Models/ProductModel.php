<?php

declare(strict_types=1);

namespace App\Models;

final class ProductModel
{
    public function all(): array
    {
        return [
            ['id' => 1, 'name' => 'Microscopio Digital', 'category' => 'laboratorio', 'stock' => 12],
            ['id' => 2, 'name' => 'Kit Reactivos Basico', 'category' => 'quimica', 'stock' => 30],
            ['id' => 3, 'name' => 'Termociclador Compacto', 'category' => 'biologia', 'stock' => 6],
        ];
    }
}
