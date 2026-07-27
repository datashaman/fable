<?php

use App\Mcp\Servers\FableServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('/mcp', FableServer::class)
    ->middleware('auth:api');
