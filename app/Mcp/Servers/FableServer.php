<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\AuditContinuityPrompt;
use App\Mcp\Prompts\BootstrapMilieuPrompt;
use App\Mcp\Prompts\ComposeStoryPrompt;
use App\Mcp\Prompts\DevelopScenarioPrompt;
use App\Mcp\Prompts\ModelEventPrompt;
use App\Mcp\Prompts\ModelKnowledgePrompt;
use App\Mcp\Resources\ContinuityResource;
use App\Mcp\Resources\MilieuResource;
use App\Mcp\Resources\PlaybookResource;
use App\Mcp\Resources\RecordResource;
use App\Mcp\Resources\SchemaResource;
use App\Mcp\Resources\WorkspaceResource;
use App\Mcp\Tools\ApplyEventEffectsTool;
use App\Mcp\Tools\ComposeStoryTool;
use App\Mcp\Tools\CurateSagaTool;
use App\Mcp\Tools\DefineClaimTool;
use App\Mcp\Tools\DefineConflictTool;
use App\Mcp\Tools\DefineOntologyTypeTool;
use App\Mcp\Tools\DefineRuleTool;
use App\Mcp\Tools\DevelopScenarioTool;
use App\Mcp\Tools\DiscloseBeliefTool;
use App\Mcp\Tools\GetChangeHistoryTool;
use App\Mcp\Tools\ManageCollaboratorTool;
use App\Mcp\Tools\RecordEventTool;
use App\Mcp\Tools\SaveContinuityTool;
use App\Mcp\Tools\SaveEntityTool;
use App\Mcp\Tools\SaveMilieuTool;
use App\Mcp\Tools\SavePerspectiveTool;
use App\Mcp\Tools\SaveRelationshipTool;
use App\Mcp\Tools\SaveSceneTool;
use App\Mcp\Tools\SearchStateTool;
use App\Mcp\Tools\SetBeliefTool;
use App\Mcp\Tools\SetGoalTool;
use App\Mcp\Tools\ValidateContinuityTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Fable Server')]
#[Version('0.1.0')]
#[Instructions('Before using any tool, you MUST read fable://playbook. Then read fable://schema and fable://workspace to discover the domain contract and accessible state. Inspect records before mutating them and supply expected_revision on every update.')]
class FableServer extends Server
{
    protected array $tools = [
        SearchStateTool::class,
        GetChangeHistoryTool::class,
        ValidateContinuityTool::class,
        SaveMilieuTool::class,
        ManageCollaboratorTool::class,
        SaveContinuityTool::class,
        DefineOntologyTypeTool::class,
        SaveEntityTool::class,
        SaveRelationshipTool::class,
        RecordEventTool::class,
        ApplyEventEffectsTool::class,
        DefineRuleTool::class,
        DefineClaimTool::class,
        SetBeliefTool::class,
        SavePerspectiveTool::class,
        DevelopScenarioTool::class,
        SetGoalTool::class,
        DefineConflictTool::class,
        ComposeStoryTool::class,
        SaveSceneTool::class,
        DiscloseBeliefTool::class,
        CurateSagaTool::class,
    ];

    protected array $resources = [
        PlaybookResource::class,
        SchemaResource::class,
        WorkspaceResource::class,
        MilieuResource::class,
        ContinuityResource::class,
        RecordResource::class,
    ];

    protected array $prompts = [
        BootstrapMilieuPrompt::class,
        ModelEventPrompt::class,
        ModelKnowledgePrompt::class,
        DevelopScenarioPrompt::class,
        ComposeStoryPrompt::class,
        AuditContinuityPrompt::class,
    ];
}
