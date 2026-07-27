<?php

namespace App\Models;

use App\Enums\CanonicalStatus;
use App\Enums\NarrativeCollectionKind;
use Database\Factories\SagaFactory;
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
 * @property string $title
 * @property NarrativeCollectionKind $kind
 * @property array<int, string>|null $overarching_conflicts
 * @property string|null $ordering_type
 * @property CanonicalStatus $canonical_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Continuity $continuity
 * @property-read Collection<int, Story> $stories
 */
#[Fillable([
    'milieu_id',
    'continuity_id',
    'title',
    'kind',
    'overarching_conflicts',
    'ordering_type',
    'canonical_status',
])]
class Saga extends Model
{
    /** @use HasFactory<SagaFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => NarrativeCollectionKind::class,
            'overarching_conflicts' => 'array',
            'canonical_status' => CanonicalStatus::class,
        ];
    }

    /**
     * Get the milieu this saga belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the continuity this saga is set in.
     *
     * @return BelongsTo<Continuity, $this>
     */
    public function continuity(): BelongsTo
    {
        return $this->belongsTo(Continuity::class);
    }

    /**
     * Get the stories that form this saga, in order.
     *
     * @return BelongsToMany<Story, $this>
     */
    public function stories(): BelongsToMany
    {
        return $this->belongsToMany(Story::class, 'saga_stories')
            ->withPivot('sequence')
            ->withTimestamps()
            ->orderByPivot('sequence');
    }
}
