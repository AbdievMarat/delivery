<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User|null $users
 *
 * @mixin Builder
 */
class Role extends Model
{
    use HasFactory;

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'users_roles');
    }

    /**
     * @return array
     */
    public static function getAvailableRoles(): array
    {
        $query = static::query();

        if (!Auth::user()->hasRole('admin')) {
            $query->where('name', '!=', 'admin');
        }

        return $query->pluck('description', 'id')->all();
    }
}
