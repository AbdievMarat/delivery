<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    /**
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function update(User $user, User $model): bool
    {
        return !$model->hasRole('admin') || $user->hasRole('admin');
    }

    /**
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function delete(User $user, User $model): bool
    {
        return $user->id === 1;
    }

    /**
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function apiTokenCreate(User $user, User $model): bool
    {
        return $model->hasRole('admin');
    }
}
