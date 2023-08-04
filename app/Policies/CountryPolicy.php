<?php

namespace App\Policies;

use App\Models\Country;
use App\Models\User;

class CountryPolicy
{
    /**
     * @param User $user
     * @param Country $country
     * @return bool
     */
    public function delete(User $user, Country $country): bool
    {
        return $user->id === User::ADMIN_USER_ID;
    }
}
