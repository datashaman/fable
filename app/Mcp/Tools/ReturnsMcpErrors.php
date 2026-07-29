<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Throwable;

trait ReturnsMcpErrors
{
    protected function mcpError(Throwable $throwable): ResponseFactory
    {
        $message = $throwable->getMessage();

        return Response::make(Response::error($message !== '' ? $message : 'An unknown error occurred.'));
    }
}
