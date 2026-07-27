<?php

namespace App\Enums;

enum GoalStatus: string
{
    case Active = 'active';
    case Achieved = 'achieved';
    case Failed = 'failed';
    case Abandoned = 'abandoned';
}
