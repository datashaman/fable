<?php

namespace App\Enums;

enum BeliefVisibility: string
{
    case Public = 'public';
    case Private = 'private';
    case Secret = 'secret';
}
