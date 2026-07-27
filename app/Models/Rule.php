<?php

namespace App\Models;

use App\Concerns\HasRevision;
use App\Enums\CanonicalStatus;
use Database\Factories\RuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $milieu_id
 * @property int $type_id
 * @property string $name
 * @property string $description
 * @property array<int, mixed>|null $conditions
 * @property array<int, mixed>|null $requirements
 * @property array<int, mixed>|null $consequences
 * @property int $priority
 * @property string|null $valid_from
 * @property string|null $valid_until
 * @property CanonicalStatus $canonical_status
 * @property array<string, mixed>|null $provenance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read OntologyType $type
 * @property-read Collection<int, OntologyType> $applicableTypes
 * @property-read Collection<int, Entity> $applicableEntities
 * @property-read Collection<int, Entity> $exceptions
 */
#[Fillable([
    'milieu_id',
    'type_id',
    'name',
    'description',
    'conditions',
    'requirements',
    'consequences',
    'priority',
    'valid_from',
    'valid_until',
    'canonical_status',
    'provenance',
])]
class Rule extends Model
{
    /** @use HasFactory<RuleFactory> */
    use HasFactory, HasRevision;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'requirements' => 'array',
            'consequences' => 'array',
            'priority' => 'integer',
            'canonical_status' => CanonicalStatus::class,
            'provenance' => 'array',
        ];
    }

    /**
     * Get the milieu this rule belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get this rule's ontology type.
     *
     * @return BelongsTo<OntologyType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(OntologyType::class, 'type_id');
    }

    /**
     * Get the ontology types this rule applies to.
     *
     * @return BelongsToMany<OntologyType, $this>
     */
    public function applicableTypes(): BelongsToMany
    {
        return $this->belongsToMany(OntologyType::class, 'rule_applicable_types');
    }

    /**
     * Get the specific entities this rule applies to.
     *
     * @return BelongsToMany<Entity, $this>
     */
    public function applicableEntities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'rule_applicable_entities');
    }

    /**
     * Get the entities exempt from this rule.
     *
     * @return BelongsToMany<Entity, $this>
     */
    public function exceptions(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'rule_exceptions')
            ->withPivot('description');
    }
}
