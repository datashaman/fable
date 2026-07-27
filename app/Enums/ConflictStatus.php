<?php

namespace App\Enums;

enum ConflictStatus: string
{
    case Unresolved = 'unresolved';
    case Escalated = 'escalated';
    case Resolved = 'resolved';
}
