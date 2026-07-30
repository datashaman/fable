<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasRevision
{
    protected static function bootHasRevision(): void
    {
        static::updating(function (Model $model): void {
            $model->setAttribute('revision', ((int) $model->getAttribute('revision')) + 1);
        });
    }
}
