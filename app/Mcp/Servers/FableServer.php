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
use App\Mcp\Tools\ApplyEventEffects;
use App\Mcp\Tools\ComposeStory;
use App\Mcp\Tools\CurateSaga;
use App\Mcp\Tools\DefineClaim;
use App\Mcp\Tools\DefineConflict;
use App\Mcp\Tools\DefineOntologyType;
use App\Mcp\Tools\DefineRule;
use App\Mcp\Tools\DevelopScenario;
use App\Mcp\Tools\DiscloseBelief;
use App\Mcp\Tools\GetChangeHistory;
use App\Mcp\Tools\ManageCollaborator;
use App\Mcp\Tools\RecordEvent;
use App\Mcp\Tools\SaveContinuity;
use App\Mcp\Tools\SaveEntity;
use App\Mcp\Tools\SaveMilieu;
use App\Mcp\Tools\SavePerspective;
use App\Mcp\Tools\SaveRelationship;
use App\Mcp\Tools\SaveScene;
use App\Mcp\Tools\SearchState;
use App\Mcp\Tools\SetBelief;
use App\Mcp\Tools\SetGoal;
use App\Mcp\Tools\ValidateContinuity;
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
        SearchState::class,
        GetChangeHistory::class,
        ValidateContinuity::class,
        SaveMilieu::class,
        ManageCollaborator::class,
        SaveContinuity::class,
        DefineOntologyType::class,
        SaveEntity::class,
        SaveRelationship::class,
        RecordEvent::class,
        ApplyEventEffects::class,
        DefineRule::class,
        DefineClaim::class,
        SetBelief::class,
        SavePerspective::class,
        DevelopScenario::class,
        SetGoal::class,
        DefineConflict::class,
        ComposeStory::class,
        SaveScene::class,
        DiscloseBelief::class,
        CurateSaga::class,
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
