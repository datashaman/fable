<?php

namespace App\Models;

use App\Enums\BeliefStance;
use App\Enums\BeliefVisibility;
use App\Enums\CanonicalStatus;
use Database\Factories\BeliefFactory;
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
 * @property int $continuity_id
 * @property int $holder_id
 * @property int $claim_id
 * @property BeliefStance $stance
 * @property float|null $confidence
 * @property string|null $acquired_at
 * @property string|null $valid_until
 * @property array<string, mixed>|null $source
 * @property BeliefVisibility|null $visibility
 * @property string|null $description
 * @property CanonicalStatus $canonical_status
 * @property array<string, mixed>|null $provenance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Continuity $continuity
 * @property-read Entity $holder
 * @property-read Claim $claim
 * @property-read Collection<int, Disclosure> $disclosures
 */
#[Fillable([
    'milieu_id',
    'continuity_id',
    'holder_id',
    'claim_id',
    'stance',
    'confidence',
    'acquired_at',
    'valid_until',
    'source',
    'visibility',
    'description',
    'canonical_status',
    'provenance',
])]
class Belief extends Model
{
    /** @use HasFactory<BeliefFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stance' => BeliefStance::class,
            'confidence' => 'float',
            'source' => 'array',
            'visibility' => BeliefVisibility::class,
            'canonical_status' => CanonicalStatus::class,
            'provenance' => 'array',
        ];
    }

    /**
     * Get the milieu this belief belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the continuity this belief belongs to.
     *
     * @return BelongsTo<Continuity, $this>
     */
    public function continuity(): BelongsTo
    {
        return $this->belongsTo(Continuity::class);
    }

    /**
     * Get the entity that holds this belief.
     *
     * @return BelongsTo<Entity, $this>
     */
    public function holder(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'holder_id');
    }

    /**
     * Get the claim this belief holds.
     *
     * @return BelongsTo<Claim, $this>
     */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    /**
     * Get the disclosures of this claim to the audience.
     *
     * @return HasMany<Disclosure, $this>
     */
    public function disclosures(): HasMany
    {
        return $this->hasMany(Disclosure::class);
    }
}
