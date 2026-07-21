<?php

declare(strict_types=1);

use App\Mcp\Servers\LearningServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/learning', LearningServer::class)
    ->middleware(['auth:sanctum', 'mcp.active', 'abilities:mcp:read', 'throttle:60,1']);
