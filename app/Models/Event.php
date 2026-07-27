<?php

namespace App\Models;

use App\Enums\CanonicalStatus;
use App\Enums\EffectType;
use Database\Factories\EventFactory;
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
 * @property int $continuity_id
 * @property int $type_id
 * @property string $name
 * @property string|null $description
 * @property string|null $start_time
 * @property string|null $end_time
 * @property array<int, mixed>|null $effects
 * @property array<int, string>|null $tags
 * @property CanonicalStatus $canonical_status
 * @property array<string, mixed>|null $provenance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Entity> $locations
 * @property-read Collection<int, Entity> $participants
 * @property-read Collection<int, Event> $causedBy
 * @property-read Collection<int, Event> $causes
 * @property-read Milieu $milieu
 * @property-read Continuity $continuity
 * @property-read OntologyType $type
 */
#[Fillable([
    'milieu_id',
    'continuity_id',
    'type_id',
    'name',
    'description',
    'start_time',
    'end_time',
    'effects',
    'tags',
    'canonical_status',
    'provenance',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'effects' => 'array',
            'tags' => 'array',
            'canonical_status' => CanonicalStatus::class,
            'provenance' => 'array',
        ];
    }

    /**
     * Get the milieu this event belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the continuity this event belongs to.
     *
     * @return BelongsTo<Continuity, $this>
     */
    public function continuity(): BelongsTo
    {
        return $this->belongsTo(Continuity::class);
    }

    /**
     * Get this event's ontology type.
     *
     * @return BelongsTo<OntologyType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(OntologyType::class, 'type_id');
    }

    /**
     * Get the entities representing the places where this event occurred.
     *
     * @return BelongsToMany<Entity, $this>
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'event_locations');
    }

    /**
     * Get the entities that took part in this event, with their roles.
     *
     * @return BelongsToMany<Entity, $this, EventParticipant>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'event_participants')
            ->using(EventParticipant::class)
            ->withPivot('role', 'attributes')
            ->withTimestamps();
    }

    /**
     * Get the earlier events that caused this event.
     *
     * @return BelongsToMany<Event, $this>
     */
    public function causedBy(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_causes', 'event_id', 'cause_event_id');
    }

    /**
     * Get the later events that this event caused.
     *
     * @return BelongsToMany<Event, $this>
     */
    public function causes(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_causes', 'cause_event_id', 'event_id');
    }

    /**
     * Apply this event's effects to the current state of its entities and relationships.
     */
    public function applyEffects(): void
    {
        foreach ($this->effects ?? [] as $effect) {
            match (EffectType::from($effect['type'])) {
                EffectType::SetAttribute => $this->applySetAttributeEffect($effect),
                EffectType::EndRelationship => $this->applyEndRelationshipEffect($effect),
                EffectType::CreateRelationship => $this->applyCreateRelationshipEffect($effect),
            };
        }
    }

    /**
     * @param  array{entity_id: int, attribute: string, value: mixed}  $effect
     */
    private function applySetAttributeEffect(array $effect): void
    {
        $entity = Entity::findOrFail($effect['entity_id']);

        $entity->update([
            'attributes' => [...($entity->getAttribute('attributes') ?? []), $effect['attribute'] => $effect['value']],
        ]);
    }

    /**
     * @param  array{relationship_id: int, ended_at?: string}  $effect
     */
    private function applyEndRelationshipEffect(array $effect): void
    {
        Relationship::whereKey($effect['relationship_id'])->update([
            'ended_at' => $effect['ended_at'] ?? $this->end_time ?? $this->start_time,
        ]);
    }

    /**
     * @param  array{relationship: array<string, mixed>}  $effect
     */
    private function applyCreateRelationshipEffect(array $effect): void
    {
        Relationship::create([
            'milieu_id' => $this->milieu_id,
            'continuity_id' => $this->continuity_id,
            'started_at' => $this->end_time ?? $this->start_time,
            'canonical_status' => CanonicalStatus::Canonical,
            ...$effect['relationship'],
        ]);
    }
}
