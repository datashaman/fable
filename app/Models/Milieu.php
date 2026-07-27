<?php

namespace App\Models;

use App\Enums\MilieuStatus;
use Database\Factories\MilieuFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $genre
 * @property array<int, string>|null $tone
 * @property array<int, string>|null $themes
 * @property string|null $current_time
 * @property string|null $time_system
 * @property string|null $spatial_scope
 * @property string|null $technological_level
 * @property string|null $supernatural_model
 * @property string|null $default_perspective
 * @property MilieuStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'description',
    'genre',
    'tone',
    'themes',
    'current_time',
    'time_system',
    'spatial_scope',
    'technological_level',
    'supernatural_model',
    'default_perspective',
    'status',
])]
class Milieu extends Model
{
    /** @use HasFactory<MilieuFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tone' => 'array',
            'themes' => 'array',
            'status' => MilieuStatus::class,
        ];
    }

    /**
     * Get the entities that belong to this milieu.
     *
     * @return HasMany<Entity, $this>
     */
    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    /**
     * Get the relationships that belong to this milieu.
     *
     * @return HasMany<Relationship, $this>
     */
    public function relationships(): HasMany
    {
        return $this->hasMany(Relationship::class);
    }

    /**
     * Get the events that belong to this milieu.
     *
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the rules that belong to this milieu.
     *
     * @return HasMany<Rule, $this>
     */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }

    /**
     * Get the beliefs that belong to this milieu.
     *
     * @return HasMany<Belief, $this>
     */
    public function beliefs(): HasMany
    {
        return $this->hasMany(Belief::class);
    }

    /**
     * Get the perspectives that belong to this milieu.
     *
     * @return HasMany<Perspective, $this>
     */
    public function perspectives(): HasMany
    {
        return $this->hasMany(Perspective::class);
    }

    /**
     * Get the continuities that belong to this milieu.
     *
     * @return HasMany<Continuity, $this>
     */
    public function continuities(): HasMany
    {
        return $this->hasMany(Continuity::class);
    }

    /**
     * Get the scenarios that belong to this milieu.
     *
     * @return HasMany<Scenario, $this>
     */
    public function scenarios(): HasMany
    {
        return $this->hasMany(Scenario::class);
    }

    /**
     * Get the stories that belong to this milieu.
     *
     * @return HasMany<Story, $this>
     */
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    /**
     * Get the sagas that belong to this milieu.
     *
     * @return HasMany<Saga, $this>
     */
    public function sagas(): HasMany
    {
        return $this->hasMany(Saga::class);
    }
}
