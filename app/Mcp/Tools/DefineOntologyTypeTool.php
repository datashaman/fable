<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('define-ontology-type')]
#[Description('Define or update a milieu-specific entity, relationship, event, or rule type.')]
class DefineOntologyTypeTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'ontology_type';
    }
}
