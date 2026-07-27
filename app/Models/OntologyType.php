<?php

namespace App\Models;

use App\Concerns\HasRevision;
use App\Enums\OntologyCategory;
use Database\Factories\OntologyTypeFactory;
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
 * @property OntologyCategory $category
 * @property string $key
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Collection<int, Entity> $entities
 * @property-read Collection<int, Relationship> $relationships
 * @property-read Collection<int, Event> $events
 * @property-read Collection<int, Rule> $rules
 */
#[Fillable([
    'milieu_id',
    'category',
    'key',
    'name',
    'description',
])]
class OntologyType extends Model
{
    /** @use HasFactory<OntologyTypeFactory> */
    use HasFactory, HasRevision;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => OntologyCategory::class,
        ];
    }

    /**
     * Get the milieu this type belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the entities classified as this ontology type.
     *
     * @return HasMany<Entity, $this>
     */
    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class, 'type_id');
    }

    /**
     * Get the relationships classified as this ontology type.
     *
     * @return HasMany<Relationship, $this>
     */
    public function relationships(): HasMany
    {
        return $this->hasMany(Relationship::class, 'type_id');
    }

    /**
     * Get the events classified as this ontology type.
     *
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'type_id');
    }

    /**
     * Get the rules classified as this ontology type.
     *
     * @return HasMany<Rule, $this>
     */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class, 'type_id');
    }

    public function instanceRelationName(): string
    {
        return match ($this->category) {
            OntologyCategory::Entity => 'entities',
            OntologyCategory::Relationship => 'relationships',
            OntologyCategory::Event => 'events',
            OntologyCategory::Rule => 'rules',
        };
    }
}
