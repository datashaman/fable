<?php

namespace App\Models;

use App\Enums\OntologyCategory;
use Database\Factories\OntologyTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $milieu_id
 * @property OntologyCategory $category
 * @property string $key
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Milieu $milieu
 */
#[Fillable([
    'milieu_id',
    'category',
    'key',
    'name',
    'description',
])]
class OntologyType extends Model
{
    /** @use HasFactory<OntologyTypeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => OntologyCategory::class,
        ];
    }

    /**
     * Get the milieu this type belongs to.
     *
     * @return BelongsTo<Milieu, $this>
     */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }
}
