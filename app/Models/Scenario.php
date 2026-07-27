<?php

namespace App\Models;

use App\Concerns\HasRevision;
use App\Enums\ScenarioStatus;
use Database\Factories\ScenarioFactory;
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
 * @property string $name
 * @property string $premise
 * @property string|null $based_on_at
 * @property array<int, string>|null $initial_conditions
 * @property array<int, string>|null $tensions
 * @property array<int, string>|null $possible_outcomes
 * @property ScenarioStatus $status
 * @property array<string, mixed>|null $provenance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Collection<int, Entity> $participants
 * @property-read Collection<int, Story> $stories
 */
#[Fillable([
    'milieu_id',
    'name',
    'premise',
    'based_on_at',
    'initial_conditions',
    'tensions',
    'possible_outcomes',
    'status',
    'provenance',
])]
class Scenario extends Model
{
    /** @use HasFactory<ScenarioFactory> */
    use HasFactory, HasRevision;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'initial_conditions' => 'array',
            'tensions' => 'array',
            'possible_outcomes' => 'array',
            'status' => ScenarioStatus::class,
            'provenance' => 'array',
        ];
    }

    /**
     * Get the milieu this scenario belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the entities involved in this scenario, with their roles.
     *
     * @return BelongsToMany<Entity, $this>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'scenario_participants')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the stories that explore an outcome of this scenario.
     *
     * @return HasMany<Story, $this>
     */
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    /**
     * Get the goals that arise within this scenario.
     *
     * @return HasMany<Goal, $this>
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Get the conflicts that arise within this scenario.
     *
     * @return HasMany<Conflict, $this>
     */
    public function conflicts(): HasMany
    {
        return $this->hasMany(Conflict::class);
    }
}
