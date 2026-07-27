<?php

namespace App\Enums;

enum NarrationMode: string
{
    case Limited = 'limited';
    case Omniscient = 'omniscient';
    case Objective = 'objective';
}
