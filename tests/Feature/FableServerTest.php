<?php

test('serves the Fable MCP server', function () {
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-11-25',
            'capabilities' => (object) [],
            'clientInfo' => [
                'name' => 'test-client',
                'version' => '1.0.0',
            ],
        ],
    ], [
        'Accept' => 'application/json, text/event-stream',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('jsonrpc', '2.0')
        ->assertJsonPath('id', 1)
        ->assertJsonPath('result.serverInfo.name', 'Fable Server')
        ->assertJsonPath('result.serverInfo.version', '0.0.1')
        ->assertJsonPath('result.instructions', 'This server exposes Fable tools, resources, and prompts.')
        ->assertHeader('MCP-Session-Id');
});
