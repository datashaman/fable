<?php

namespace App\Models;

use Database\Factories\ChangeSetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['milieu_id', 'user_id', 'tool_name', 'summary', 'metadata'])]
class ChangeSet extends Model
{
    /** @use HasFactory<ChangeSetFactory> */
    use HasFactory, HasUlids;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    /** @return BelongsTo<Milieu, $this> */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ChangeEntry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(ChangeEntry::class);
    }
}
