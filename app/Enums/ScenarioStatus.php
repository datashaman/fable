<?php

namespace App\Enums;

enum ScenarioStatus: string
{
    case Hypothetical = 'hypothetical';
    case Selected = 'selected';
    case Discarded = 'discarded';
}
