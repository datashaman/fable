<?php

namespace App\Models;

use App\Concerns\HasRevision;
use Database\Factories\PerspectiveFactory;
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
 * @property string $name
 * @property int|null $holder_id
 * @property array<int, string>|null $biases
 * @property string|null $temporal_position
 * @property string|null $description
 * @property array<string, mixed>|null $provenance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Continuity $continuity
 * @property-read Entity|null $holder
 * @property-read Collection<int, Belief> $beliefs
 * @property-read Collection<int, Entity> $knownEntities
 * @property-read Collection<int, Event> $knownEvents
 */
#[Fillable([
    'milieu_id',
    'continuity_id',
    'name',
    'holder_id',
    'biases',
    'temporal_position',
    'description',
    'provenance',
])]
class Perspective extends Model
{
    /** @use HasFactory<PerspectiveFactory> */
    use HasFactory, HasRevision;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'biases' => 'array',
            'provenance' => 'array',
        ];
    }

    /**
     * Get the milieu this perspective belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the continuity this perspective is held within.
     *
     * @return BelongsTo<Continuity, $this>
     */
    public function continuity(): BelongsTo
    {
        return $this->belongsTo(Continuity::class);
    }

    /**
     * Get the entity whose viewpoint this perspective represents.
     *
     * @return BelongsTo<Entity, $this>
     */
    public function holder(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'holder_id');
    }

    /**
     * Get the beliefs associated with this perspective's holder.
     *
     * @return BelongsToMany<Belief, $this>
     */
    public function beliefs(): BelongsToMany
    {
        return $this->belongsToMany(Belief::class, 'perspective_beliefs');
    }

    /**
     * Get the entities known to this perspective.
     *
     * @return BelongsToMany<Entity, $this>
     */
    public function knownEntities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'perspective_known_entities');
    }

    /**
     * Get the events known to this perspective.
     *
     * @return BelongsToMany<Event, $this>
     */
    public function knownEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'perspective_known_events');
    }
}
