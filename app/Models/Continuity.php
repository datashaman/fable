<?php

namespace App\Models;

use App\Enums\CanonicalStatus;
use Database\Factories\ContinuityFactory;
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
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $description
 * @property int|null $diverged_from_event_id
 * @property CanonicalStatus $canonical_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Continuity|null $parent
 * @property-read Event|null $divergedFromEvent
 * @property-read Collection<int, Continuity> $branches
 * @property-read Collection<int, Event> $events
 * @property-read Collection<int, Relationship> $relationships
 * @property-read Collection<int, Belief> $beliefs
 * @property-read Collection<int, Story> $stories
 * @property-read Collection<int, Saga> $sagas
 */
#[Fillable([
    'milieu_id',
    'parent_id',
    'name',
    'description',
    'diverged_from_event_id',
    'canonical_status',
])]
class Continuity extends Model
{
    /** @use HasFactory<ContinuityFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'canonical_status' => CanonicalStatus::class,
        ];
    }

    /**
     * Get the milieu this continuity belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the continuity this one diverged from.
     *
     * @return BelongsTo<Continuity, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Continuity::class, 'parent_id');
    }

    /**
     * Get the continuities that diverged from this one.
     *
     * @return HasMany<Continuity, $this>
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Continuity::class, 'parent_id');
    }

    /**
     * Get the event this continuity diverged from its parent at.
     *
     * @return BelongsTo<Event, $this>
     */
    public function divergedFromEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'diverged_from_event_id');
    }

    /**
     * Get the events that belong to this continuity.
     *
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the relationships that belong to this continuity.
     *
     * @return HasMany<Relationship, $this>
     */
    public function relationships(): HasMany
    {
        return $this->hasMany(Relationship::class);
    }

    /**
     * Get the beliefs that belong to this continuity.
     *
     * @return HasMany<Belief, $this>
     */
    public function beliefs(): HasMany
    {
        return $this->hasMany(Belief::class);
    }

    /**
     * Get the stories set in this continuity.
     *
     * @return HasMany<Story, $this>
     */
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    /**
     * Get the sagas set in this continuity.
     *
     * @return HasMany<Saga, $this>
     */
    public function sagas(): HasMany
    {
        return $this->hasMany(Saga::class);
    }

    /**
     * Get the goals held within this continuity.
     *
     * @return HasMany<Goal, $this>
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }
}
