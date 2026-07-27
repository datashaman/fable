<?php

namespace App\Models;

use App\Enums\CanonicalStatus;
use App\Enums\EntityType;
use Database\Factories\EntityFactory;
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
 * @property EntityType $type
 * @property string $name
 * @property string|null $description
 * @property array<int, string>|null $aliases
 * @property array<string, mixed>|null $attributes
 * @property array<int, string>|null $tags
 * @property string|null $existed_from
 * @property string|null $ended_at
 * @property CanonicalStatus $canonical_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Collection<int, Relationship> $sourceRelationships
 * @property-read Collection<int, Relationship> $targetRelationships
 */
#[Fillable([
    'milieu_id',
    'type',
    'name',
    'description',
    'aliases',
    'attributes',
    'tags',
    'existed_from',
    'ended_at',
    'canonical_status',
])]
class Entity extends Model
{
    /** @use HasFactory<EntityFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EntityType::class,
            'aliases' => 'array',
            'attributes' => 'array',
            'tags' => 'array',
            'canonical_status' => CanonicalStatus::class,
        ];
    }

    /**
     * Get the milieu this entity belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the relationships that originate from this entity.
     *
     * @return HasMany<Relationship, $this>
     */
    public function sourceRelationships(): HasMany
    {
        return $this->hasMany(Relationship::class, 'source_id');
    }

    /**
     * Get the relationships that point to this entity.
     *
     * @return HasMany<Relationship, $this>
     */
    public function targetRelationships(): HasMany
    {
        return $this->hasMany(Relationship::class, 'target_id');
    }
}
