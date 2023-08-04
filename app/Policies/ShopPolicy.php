<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    /**
     * @param User $user
     * @param Shop $shop
     * @return bool
     */
    public function delete(User $user, Shop $shop): bool
    {
        return $user->id === User::ADMIN_USER_ID;
    }
}
