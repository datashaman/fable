<?php

namespace App\Enums;

enum NarrativeCollectionKind: string
{
    case Saga = 'saga';
    case Series = 'series';
    case Cycle = 'cycle';
    case Anthology = 'anthology';
}
