<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Define or update a milieu-specific entity, relationship, event, or rule type.')]
class DefineOntologyType extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'ontology_type';
    }
}
