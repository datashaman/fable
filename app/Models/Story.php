<?php

namespace App\Models;

use App\Enums\CanonicalStatus;
use App\Enums\NarrativeForm;
use Database\Factories\StoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property CanonicalStatus $canonical_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Continuity $continuity
 * @property-read Scenario|null $scenario
 * @property-read Collection<int, Event> $events
 * @property-read Collection<int, Perspective> $perspectives
 * @property-read Collection<int, Scene> $scenes
 * @property-read Collection<int, Saga> $sagas
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
    'canonical_status',
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
            'canonical_status' => CanonicalStatus::class,
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
}
