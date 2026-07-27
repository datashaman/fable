<?php

namespace App\Models;

use App\Concerns\HasRevision;
use App\Enums\MilieuRole;
use App\Enums\MilieuStatus;
use Database\Factories\MilieuFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $owner_id
 * @property int $revision
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
 * @property-read User|null $owner
 * @property-read Collection<int, MilieuMembership> $memberships
 * @property-read Collection<int, Continuity> $continuities
 */
#[Fillable([
    'name',
    'owner_id',
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
    use HasFactory, HasRevision;

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<MilieuMembership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(MilieuMembership::class);
    }

    public function roleFor(User $user): ?string
    {
        if ($this->owner_id === $user->id) {
            return 'owner';
        }

        $role = $this->memberships()->where('user_id', $user->id)->first()?->role;

        return $role instanceof MilieuRole ? $role->value : $role;
    }

    public function canView(User $user): bool
    {
        return $this->roleFor($user) !== null;
    }

    public function canEdit(User $user): bool
    {
        return in_array($this->roleFor($user), ['owner', MilieuRole::Editor->value], true);
    }

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

    /**
     * Get the goals that belong to this milieu.
     *
     * @return HasMany<Goal, $this>
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Get the conflicts that belong to this milieu.
     *
     * @return HasMany<Conflict, $this>
     */
    public function conflicts(): HasMany
    {
        return $this->hasMany(Conflict::class);
    }

    /**
     * Get the claims that belong to this milieu.
     *
     * @return HasMany<Claim, $this>
     */
    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * Get the ontology types that belong to this milieu.
     *
     * @return HasMany<OntologyType, $this>
     */
    public function ontologyTypes(): HasMany
    {
        return $this->hasMany(OntologyType::class);
    }
}
