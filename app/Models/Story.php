<?php

namespace App\Models;

use App\Enums\CanonicalStatus;
use App\Enums\NarrationMode;
use App\Enums\NarrationPerson;
use App\Enums\NarrationReliability;
use App\Enums\NarrativeForm;
use Database\Factories\StoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $milieu_id
 * @property int $continuity_id
 * @property int|null $scenario_id
 * @property string $title
 * @property NarrativeForm $form
 * @property string|null $starts_at
 * @property string|null $ends_at
 * @property array<int, string>|null $themes
 * @property NarrationPerson|null $narration_person
 * @property NarrationMode|null $narration_mode
 * @property int|null $focalizer_id
 * @property int|null $narrator_id
 * @property NarrationReliability|null $narration_reliability
 * @property CanonicalStatus $canonical_status
 * @property array<string, mixed>|null $provenance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Continuity $continuity
 * @property-read Scenario|null $scenario
 * @property-read Entity|null $focalizer
 * @property-read Entity|null $narrator
 * @property-read Collection<int, Event> $events
 * @property-read Collection<int, Perspective> $perspectives
 * @property-read Collection<int, Scene> $scenes
 * @property-read Collection<int, Saga> $sagas
 * @property-read Collection<int, Disclosure> $disclosures
 */
#[Fillable([
    'milieu_id',
    'continuity_id',
    'scenario_id',
    'title',
    'form',
    'starts_at',
    'ends_at',
    'themes',
    'narration_person',
    'narration_mode',
    'focalizer_id',
    'narrator_id',
    'narration_reliability',
    'canonical_status',
    'provenance',
])]
class Story extends Model
{
    /** @use HasFactory<StoryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'form' => NarrativeForm::class,
            'themes' => 'array',
            'narration_person' => NarrationPerson::class,
            'narration_mode' => NarrationMode::class,
            'narration_reliability' => NarrationReliability::class,
            'canonical_status' => CanonicalStatus::class,
            'provenance' => 'array',
        ];
    }

    /**
     * Get the milieu this story belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the continuity this story is set in.
     *
     * @return BelongsTo<Continuity, $this>
     */
    public function continuity(): BelongsTo
    {
        return $this->belongsTo(Continuity::class);
    }

    /**
     * Get the scenario this story explores an outcome of.
     *
     * @return BelongsTo<Scenario, $this>
     */
    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class);
    }

    /**
     * Get the entity whose vantage the narration follows.
     *
     * @return BelongsTo<Entity, $this>
     */
    public function focalizer(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'focalizer_id');
    }

    /**
     * Get the entity that narrates this story, if any.
     *
     * @return BelongsTo<Entity, $this>
     */
    public function narrator(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'narrator_id');
    }

    /**
     * Get the events this story presents, in narrative order.
     *
     * @return BelongsToMany<Event, $this>
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'story_events')
            ->withPivot('sequence')
            ->withTimestamps()
            ->orderByPivot('sequence');
    }

    /**
     * Get the perspectives this story is told from.
     *
     * @return BelongsToMany<Perspective, $this>
     */
    public function perspectives(): BelongsToMany
    {
        return $this->belongsToMany(Perspective::class, 'story_perspectives');
    }

    /**
     * Get the scenes that structure this story.
     *
     * @return HasMany<Scene, $this>
     */
    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class)->orderBy('sequence');
    }

    /**
     * Get the sagas this story is part of.
     *
     * @return BelongsToMany<Saga, $this>
     */
    public function sagas(): BelongsToMany
    {
        return $this->belongsToMany(Saga::class, 'saga_stories')
            ->withPivot('sequence')
            ->withTimestamps();
    }

    /**
     * Get the disclosures made to the audience across this story's scenes.
     *
     * @return HasManyThrough<Disclosure, Scene, $this>
     */
    public function disclosures(): HasManyThrough
    {
        return $this->hasManyThrough(Disclosure::class, Scene::class);
    }
}
