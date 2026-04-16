<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\HookConfigService;

final class ConfigController
{
    public function getFlags(Request $request): void
    {
        $flags = (new HookConfigService())->getFlags();
        Response::json(['flags' => $flags]);
    }

    public function saveFlags(Request $request): void
    {
        $flags = $request->allBody()['flags'] ?? [];

        if (!is_array($flags)) {
            Response::json(['error' => 'Invalid flags payload'], 422);
        }

        $saved = (new HookConfigService())->saveFlags($flags);
        Response::json(['flags' => $saved]);
    }
}
