<?php

namespace App\Models;

use App\Concerns\HasRevision;
use Database\Factories\ClaimFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $milieu_id
 * @property int $subject_id
 * @property string $predicate
 * @property int|null $object_id
 * @property string|null $object_value
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Entity $subject
 * @property-read Entity|null $object
 * @property-read Collection<int, Belief> $beliefs
 */
#[Fillable([
    'milieu_id',
    'subject_id',
    'predicate',
    'object_id',
    'object_value',
    'description',
])]
class Claim extends Model
{
    /** @use HasFactory<ClaimFactory> */
    use HasFactory, HasRevision;

    /**
     * Get the milieu this claim belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the entity this claim is about.
     *
     * @return BelongsTo<Entity, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'subject_id');
    }

    /**
     * Get the entity this claim points to, if the object is an entity.
     *
     * @return BelongsTo<Entity, $this>
     */
    public function object(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'object_id');
    }

    /**
     * Get the beliefs that hold this claim.
     *
     * @return HasMany<Belief, $this>
     */
    public function beliefs(): HasMany
    {
        return $this->hasMany(Belief::class);
    }
}
