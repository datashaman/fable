<?php

namespace App\Livewire;

use App\Livewire\Concerns\ListensForStateChanges;
use Livewire\Component;

abstract class ReadonlyPage extends Component
{
    use ListensForStateChanges;
}
