<?php

namespace App\Support\Fable;

use Illuminate\Database\Eloquent\Model;

class StatePresenter
{
    /** @return array<string, mixed> */
    public function record(string $type, Model $record): array
    {
        return [
            'type' => $type,
            'id' => $record->getKey(),
            'revision' => $record->getAttribute('revision'),
            'attributes' => $record->attributesToArray(),
        ];
    }
}
