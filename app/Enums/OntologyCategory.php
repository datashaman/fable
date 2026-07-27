<?php

namespace App\Enums;

enum OntologyCategory: string
{
    case Entity = 'entity';
    case Relationship = 'relationship';
    case Event = 'event';
    case Rule = 'rule';
}
