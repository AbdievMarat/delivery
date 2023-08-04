<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $available_countries
 * @property string|null $attached_shops
 * @property string|null $access_token
 * @property Carbon $email_verified_at
 * @property string $password
 * @property boolean $active
 * @property string $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Role|null $roles
 *
 * @mixin Builder
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'available_countries',
        'attached_shops',
        'active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'available_countries' => 'json',
        'attached_shops' => 'json',
    ];

    const ADMIN_USER_ID = 1;
    const MOBILE_APPLICATION_USER_ID = 3;
    const YANDEX_USER_ID = 6;

    const ACTIVE_USER = 1;

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    /**
     * @param $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        return $this->roles()->where('name', '=', $role)->exists();
    }

    /**
     * @param Builder $query
     * @param array $data
     * @return void
     */
    public function scopeFilter(Builder $query, array $data): void
    {
        if (isset($data['id'])) {
            $query->where("{$this->getTable()}.id", '=', $data['id']);
        }

        if (isset($data['name'])) {
            $query->where("{$this->getTable()}.name", 'LIKE', '%' . $data['name'] . '%');
        }

        if (isset($data['email'])) {
            $query->where("{$this->getTable()}.email", 'LIKE', '%' . $data['email'] . '%');
        }

        if (isset($data['active'])) {
            $query->where("{$this->getTable()}.active", '=', $data['active']);
        }
    }
}
