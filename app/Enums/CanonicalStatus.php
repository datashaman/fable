<?php

namespace App\Enums;

enum CanonicalStatus: string
{
    case Canonical = 'canonical';
    case Proposed = 'proposed';
    case Disputed = 'disputed';
    case Obsolete = 'obsolete';
}
