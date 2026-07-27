<?php

namespace App\Enums;

enum RuleType: string
{
    case Physical = 'physical';
    case Biological = 'biological';
    case Metaphysical = 'metaphysical';
    case Magical = 'magical';
    case Technological = 'technological';
    case Social = 'social';
    case Legal = 'legal';
    case Religious = 'religious';
}
