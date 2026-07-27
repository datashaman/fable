<?php

namespace App\Models;

use App\Enums\CanonicalStatus;
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
 * @property int $type_id
 * @property string $name
 * @property string|null $description
 * @property array<int, string>|null $aliases
 * @property array<string, mixed>|null $attributes
 * @property array<int, string>|null $tags
 * @property string|null $existed_from
 * @property string|null $ended_at
 * @property CanonicalStatus $canonical_status
 * @property array<string, mixed>|null $provenance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read OntologyType $type
 * @property-read Collection<int, Relationship> $sourceRelationships
 * @property-read Collection<int, Relationship> $targetRelationships
 */
#[Fillable([
    'milieu_id',
    'type_id',
    'name',
    'description',
    'aliases',
    'attributes',
    'tags',
    'existed_from',
    'ended_at',
    'canonical_status',
    'provenance',
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
            'aliases' => 'array',
            'attributes' => 'array',
            'tags' => 'array',
            'canonical_status' => CanonicalStatus::class,
            'provenance' => 'array',
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
     * Get this entity's ontology type.
     *
     * @return BelongsTo<OntologyType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(OntologyType::class, 'type_id');
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

    /**
     * Get the goals held by this entity.
     *
     * @return HasMany<Goal, $this>
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class, 'holder_id');
    }

    /**
     * Get the conflicts in which this entity is the contested subject.
     *
     * @return HasMany<Conflict, $this>
     */
    public function conflictsAsSubject(): HasMany
    {
        return $this->hasMany(Conflict::class, 'subject_id');
    }

    /**
     * Get the claims this entity is the subject of.
     *
     * @return HasMany<Claim, $this>
     */
    public function claimsAsSubject(): HasMany
    {
        return $this->hasMany(Claim::class, 'subject_id');
    }

    /**
     * Get the claims this entity is the object of.
     *
     * @return HasMany<Claim, $this>
     */
    public function claimsAsObject(): HasMany
    {
        return $this->hasMany(Claim::class, 'object_id');
    }
}
