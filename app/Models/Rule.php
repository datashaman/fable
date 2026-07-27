<?php

namespace App\Models;

use App\Enums\CanonicalStatus;
use App\Enums\RuleType;
use Database\Factories\RuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $milieu_id
 * @property RuleType $type
 * @property string $name
 * @property string $description
 * @property array<string, mixed>|null $scope
 * @property array<int, mixed>|null $conditions
 * @property array<int, mixed>|null $requirements
 * @property array<int, mixed>|null $consequences
 * @property array<int, mixed>|null $exceptions
 * @property int $priority
 * @property CanonicalStatus $canonical_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 */
#[Fillable([
    'milieu_id',
    'type',
    'name',
    'description',
    'scope',
    'conditions',
    'requirements',
    'consequences',
    'exceptions',
    'priority',
    'canonical_status',
])]
class Rule extends Model
{
    /** @use HasFactory<RuleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => RuleType::class,
            'scope' => 'array',
            'conditions' => 'array',
            'requirements' => 'array',
            'consequences' => 'array',
            'exceptions' => 'array',
            'priority' => 'integer',
            'canonical_status' => CanonicalStatus::class,
        ];
    }

    /**
     * Get the milieu this rule belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }
}
