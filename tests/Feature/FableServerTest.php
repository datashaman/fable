<?php

use App\Mcp\Resources\PlaybookResource;
use App\Mcp\Servers\FableServer;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;

beforeEach(function () {
    Passport::actingAs(User::factory()->create(), ['mcp:use']);
});

test('protects the MCP endpoint with the Passport API guard', function () {
    $route = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($route) => $route->uri() === 'mcp' && in_array('POST', $route->methods(), true));

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->toContain('auth:api');
});

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
        ->assertJsonPath('result.serverInfo.version', '0.1.0')
        ->assertJsonPath('result.instructions', 'Before using any tool, you MUST read fable://playbook. Then read fable://schema and fable://workspace to discover the domain contract and accessible state. Inspect records before mutating them and supply expected_revision on every update.')
        ->assertHeader('MCP-Session-Id');
});

test('provides the required Fable playbook resource', function () {
    $resource = app(PlaybookResource::class);

    expect($resource->uri())->toBe('fable://playbook')
        ->and($resource->mimeType())->toBe('text/markdown');

    FableServer::resource($resource)
        ->assertOk()
        ->assertName('fable-playbook')
        ->assertTitle('Fable Domain Playbook')
        ->assertDescription('Required guide to Fable\'s narrative domain model, invariants, and workflow.')
        ->assertSee([
            '# Fable Domain Playbook',
            '## Domain map',
            '## Required workflow',
            'Keep world chronology, story event order, scene order, character knowledge, and audience disclosure order distinct.',
        ]);
});

test('advertises the playbook resource to MCP clients', function () {
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'resources/list',
        'params' => (object) [],
    ], [
        'Accept' => 'application/json, text/event-stream',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('result.resources.0.name', 'fable-playbook')
        ->assertJsonPath('result.resources.0.uri', 'fable://playbook')
        ->assertJsonPath('result.resources.0.mimeType', 'text/markdown');
});

test('advertises the management tools, resource templates, and guided prompts', function () {
    $headers = ['Accept' => 'application/json, text/event-stream'];

    $tools = $this->postJson('/mcp', [
        'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list', 'params' => (object) [],
    ], $headers)->assertOk();
    $toolNames = collect($tools->json('result.tools'))->pluck('name');

    if ($cursor = $tools->json('result.nextCursor')) {
        $nextTools = $this->postJson('/mcp', [
            'jsonrpc' => '2.0', 'id' => 4, 'method' => 'tools/list', 'params' => ['cursor' => $cursor],
        ], $headers)->assertOk();
        $toolNames = $toolNames->merge(collect($nextTools->json('result.tools'))->pluck('name'));
    }

    expect($toolNames)->toContain(
        'search-state',
        'save-milieu',
        'record-event',
        'apply-event-effects',
        'compose-story',
        'curate-saga',
    );

    $templates = $this->postJson('/mcp', [
        'jsonrpc' => '2.0', 'id' => 2, 'method' => 'resources/templates/list', 'params' => (object) [],
    ], $headers)->assertOk();
    $templateUris = collect($templates->json('result.resourceTemplates'))->pluck('uriTemplate');

    expect($templateUris)->toContain(
        'fable://milieus/{milieuId}',
        'fable://continuities/{continuityId}',
        'fable://records/{recordType}/{recordId}',
    );

    $prompts = $this->postJson('/mcp', [
        'jsonrpc' => '2.0', 'id' => 3, 'method' => 'prompts/list', 'params' => (object) [],
    ], $headers)->assertOk();
    $promptNames = collect($prompts->json('result.prompts'))->pluck('name');

    expect($promptNames)->toContain('bootstrap-milieu', 'model-knowledge', 'compose-story', 'audit-continuity');
});
