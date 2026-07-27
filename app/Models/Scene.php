<?php

namespace App\Models;

use Database\Factories\SceneFactory;
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
 * @property int $story_id
 * @property string $name
 * @property string|null $description
 * @property int $sequence
 * @property array<string, mixed>|null $provenance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Story $story
 * @property-read Collection<int, Event> $events
 * @property-read Collection<int, Disclosure> $disclosures
 */
#[Fillable([
    'story_id',
    'name',
    'description',
    'sequence',
    'provenance',
])]
class Scene extends Model
{
    /** @use HasFactory<SceneFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'provenance' => 'array',
        ];
    }

    /**
     * Get the story this scene belongs to.
     *
     * @return BelongsTo<Story, $this>
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * Get the events this scene presents.
     *
     * @return BelongsToMany<Event, $this>
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'scene_events');
    }

    /**
     * Get the disclosures made to the audience in this scene.
     *
     * @return HasMany<Disclosure, $this>
     */
    public function disclosures(): HasMany
    {
        return $this->hasMany(Disclosure::class);
    }
}
