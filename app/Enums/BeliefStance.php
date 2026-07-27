<?php

namespace App\Enums;

enum BeliefStance: string
{
    case Accepts = 'accepts';
    case Rejects = 'rejects';
    case Uncertain = 'uncertain';
}
