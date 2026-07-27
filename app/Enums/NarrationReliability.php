<?php

namespace App\Enums;

enum NarrationReliability: string
{
    case Reliable = 'reliable';
    case MostlyReliable = 'mostly_reliable';
    case Unreliable = 'unreliable';
}
