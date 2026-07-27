<?php

namespace App\Models;

use App\Enums\ConflictStatus;
use Database\Factories\ConflictFactory;
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
 * @property int|null $scenario_id
 * @property int|null $subject_id
 * @property string|null $description
 * @property ConflictStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Continuity $continuity
 * @property-read Scenario|null $scenario
 * @property-read Entity|null $subject
 * @property-read Collection<int, Goal> $goals
 * @property-read Collection<int, Saga> $sagas
 */
#[Fillable([
    'milieu_id',
    'continuity_id',
    'scenario_id',
    'subject_id',
    'description',
    'status',
])]
class Conflict extends Model
{
    /** @use HasFactory<ConflictFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ConflictStatus::class,
        ];
    }

    /**
     * Get the milieu this conflict belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the continuity this conflict is held within.
     *
     * @return BelongsTo<Continuity, $this>
     */
    public function continuity(): BelongsTo
    {
        return $this->belongsTo(Continuity::class);
    }

    /**
     * Get the scenario this conflict arises within, if any.
     *
     * @return BelongsTo<Scenario, $this>
     */
    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class);
    }

    /**
     * Get the entity that is being contested, if any.
     *
     * @return BelongsTo<Entity, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'subject_id');
    }

    /**
     * Get the incompatible goals that make up this conflict.
     *
     * @return BelongsToMany<Goal, $this>
     */
    public function goals(): BelongsToMany
    {
        return $this->belongsToMany(Goal::class, 'conflict_goals');
    }

    /**
     * Get the sagas in which this conflict recurs.
     *
     * @return BelongsToMany<Saga, $this>
     */
    public function sagas(): BelongsToMany
    {
        return $this->belongsToMany(Saga::class, 'saga_conflicts');
    }
}
