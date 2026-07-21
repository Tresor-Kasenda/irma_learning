<?php

declare(strict_types=1);

use App\Mcp\Servers\LearningServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/learning', LearningServer::class)
    ->middleware(['auth:sanctum', 'throttle:60,1']);
