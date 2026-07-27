<?php

namespace App\Models;

use App\Enums\GoalStatus;
use Database\Factories\GoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $milieu_id
 * @property int $continuity_id
 * @property int|null $scenario_id
 * @property int $holder_id
 * @property string $objective
 * @property string|null $motivation
 * @property array<string, mixed>|null $stakes
 * @property GoalStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Continuity $continuity
 * @property-read Scenario|null $scenario
 * @property-read Entity $holder
 */
#[Fillable([
    'milieu_id',
    'continuity_id',
    'scenario_id',
    'holder_id',
    'objective',
    'motivation',
    'stakes',
    'status',
])]
class Goal extends Model
{
    /** @use HasFactory<GoalFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stakes' => 'array',
            'status' => GoalStatus::class,
        ];
    }

    /**
     * Get the milieu this goal belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the continuity this goal is held within.
     *
     * @return BelongsTo<Continuity, $this>
     */
    public function continuity(): BelongsTo
    {
        return $this->belongsTo(Continuity::class);
    }

    /**
     * Get the scenario this goal arises within, if any.
     *
     * @return BelongsTo<Scenario, $this>
     */
    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class);
    }

    /**
     * Get the entity that holds this goal.
     *
     * @return BelongsTo<Entity, $this>
     */
    public function holder(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'holder_id');
    }
}
