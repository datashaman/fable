<?php

namespace App\Models;

use App\Enums\BeliefStance;
use App\Enums\BeliefVisibility;
use App\Enums\CanonicalStatus;
use Database\Factories\BeliefFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $milieu_id
 * @property int $holder_id
 * @property array<string, mixed> $claim
 * @property BeliefStance $stance
 * @property float|null $confidence
 * @property string|null $acquired_at
 * @property array<string, mixed>|null $source
 * @property BeliefVisibility|null $visibility
 * @property string|null $description
 * @property CanonicalStatus $canonical_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Entity $holder
 */
#[Fillable([
    'milieu_id',
    'holder_id',
    'claim',
    'stance',
    'confidence',
    'acquired_at',
    'source',
    'visibility',
    'description',
    'canonical_status',
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
            'claim' => 'array',
            'stance' => BeliefStance::class,
            'confidence' => 'float',
            'source' => 'array',
            'visibility' => BeliefVisibility::class,
            'canonical_status' => CanonicalStatus::class,
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
     * Get the entity that holds this belief.
     *
     * @return BelongsTo<Entity, $this>
     */
    public function holder(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'holder_id');
    }
}
