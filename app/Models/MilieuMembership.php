<?php

namespace App\Models;

use App\Enums\MilieuRole;
use Database\Factories\MilieuMembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $milieu_id
 * @property int $user_id
 * @property MilieuRole $role
 * @property-read Milieu $milieu
 * @property-read User $user
 */
#[Fillable(['milieu_id', 'user_id', 'role'])]
class MilieuMembership extends Model
{
    /** @use HasFactory<MilieuMembershipFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['role' => MilieuRole::class];
    }

    /** @return BelongsTo<Milieu, $this> */
    public function milieu(): BelongsTo
    {
        return $this->belongsTo(Milieu::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
