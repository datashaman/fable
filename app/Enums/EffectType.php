<?php

namespace App\Enums;

enum EffectType: string
{
    case SetAttribute = 'set_attribute';
    case EndRelationship = 'end_relationship';
    case CreateRelationship = 'create_relationship';
}
