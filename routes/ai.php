<?php

use App\Mcp\Servers\FableServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::local('fable', FableServer::class);

Mcp::web('/mcp', FableServer::class)
    ->middleware('auth:api');
