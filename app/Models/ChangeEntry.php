<?php

namespace App\Models;

use App\Events\StateChanged;
use Database\Factories\ChangeEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property array<string, mixed>|null $before
 * @property array<string, mixed>|null $after
 */
#[Fillable(['change_set_id', 'record_type', 'record_id', 'action', 'before', 'after'])]
class ChangeEntry extends Model
{
    /** @use HasFactory<ChangeEntryFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected static function booted(): void
    {
        static::created(fn (ChangeEntry $changeEntry) => StateChanged::dispatch($changeEntry->getKey()));
    }

    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array'];
    }

    /** @return BelongsTo<ChangeSet, $this> */
    public function changeSet(): BelongsTo
    {
        return $this->belongsTo(ChangeSet::class);
    }
}
