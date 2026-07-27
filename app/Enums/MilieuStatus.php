<?php

namespace App\Enums;

enum MilieuStatus: string
{
    case Draft = 'draft';
    case Canonical = 'canonical';
    case Evolving = 'evolving';
    case Archived = 'archived';
}
