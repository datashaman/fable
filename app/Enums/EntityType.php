<?php

namespace App\Enums;

enum EntityType: string
{
    case Character = 'character';
    case Group = 'group';
    case Place = 'place';
    case Object = 'object';
    case Species = 'species';
    case Culture = 'culture';
    case Concept = 'concept';
}
