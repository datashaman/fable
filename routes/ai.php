<?php

use App\Mcp\Servers\FableServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', FableServer::class);
