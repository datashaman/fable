<?php

use App\Models\User;

test('the OAuth authorization view presents Fable styled consent details', function () {
    $html = view('mcp.authorize', [
        'authToken' => 'signed-authorization-request',
        'client' => (object) [
            'id' => 'client-id',
            'name' => 'Codex',
        ],
        'scopes' => [
            (object) ['description' => 'Use MCP server'],
        ],
        'user' => User::factory()->make([
            'email' => 'marlinf@datashaman.com',
        ]),
    ])->render();

    expect($html)
        ->toContain('fable-consent-shell')
        ->toContain('MCP access')
        ->toContain('Authorize Codex')
        ->toContain('marlinf@datashaman.com')
        ->toContain('Use MCP server')
        ->toContain('fable-consent-button-secondary')
        ->toContain('fable-consent-button-primary')
        ->toContain(route('passport.authorizations.deny'))
        ->toContain(route('passport.authorizations.approve'));
});
