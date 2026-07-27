<?php

namespace App\Models;

use App\Enums\CanonicalStatus;
use App\Enums\RelationshipType;
use Database\Factories\RelationshipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $milieu_id
 * @property int $continuity_id
 * @property RelationshipType $type
 * @property string|null $inverse
 * @property bool $symmetric
 * @property int $source_id
 * @property int $target_id
 * @property string|null $description
 * @property array<string, mixed>|null $attributes
 * @property string|null $started_at
 * @property string|null $ended_at
 * @property CanonicalStatus $canonical_status
 * @property array<string, mixed>|null $provenance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Continuity $continuity
 * @property-read Entity $source
 * @property-read Entity $target
 */
#[Fillable([
    'milieu_id',
    'continuity_id',
    'type',
    'inverse',
    'symmetric',
    'source_id',
    'target_id',
    'description',
    'attributes',
    'started_at',
    'ended_at',
    'canonical_status',
    'provenance',
])]
class Relationship extends Model
{
    /** @use HasFactory<RelationshipFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => RelationshipType::class,
            'symmetric' => 'boolean',
            'attributes' => 'array',
            'canonical_status' => CanonicalStatus::class,
            'provenance' => 'array',
        ];
    }

    /**
     * Get the milieu this relationship belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the continuity this relationship belongs to.
     *
     * @return BelongsTo<Continuity, $this>
     */
    public function continuity(): BelongsTo
    {
        return $this->belongsTo(Continuity::class);
    }

    /**
     * Get the entity this relationship originates from.
     *
     * @return BelongsTo<Entity, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'source_id');
    }

    /**
     * Get the entity this relationship points to.
     *
     * @return BelongsTo<Entity, $this>
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'target_id');
    }
}
