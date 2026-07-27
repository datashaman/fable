<?php

namespace App\Models;

use App\Concerns\HasRevision;
use Database\Factories\DisclosureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $milieu_id
 * @property int $continuity_id
 * @property int $belief_id
 * @property int $scene_id
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 * @property-read Continuity $continuity
 * @property-read Belief $belief
 * @property-read Scene $scene
 */
#[Fillable([
    'milieu_id',
    'continuity_id',
    'belief_id',
    'scene_id',
    'description',
])]
class Disclosure extends Model
{
    /** @use HasFactory<DisclosureFactory> */
    use HasFactory, HasRevision;

    /**
     * Get the milieu this disclosure belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /**
     * Get the continuity this disclosure belongs to.
     *
     * @return BelongsTo<Continuity, $this>
     */
    public function continuity(): BelongsTo
    {
        return $this->belongsTo(Continuity::class);
    }

    /**
     * Get the claim being disclosed to the audience.
     *
     * @return BelongsTo<Belief, $this>
     */
    public function belief(): BelongsTo
    {
        return $this->belongsTo(Belief::class);
    }

    /**
     * Get the scene in which the audience learns this claim.
     *
     * @return BelongsTo<Scene, $this>
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }
}
