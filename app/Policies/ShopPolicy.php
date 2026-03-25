<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shop;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view/manage the shop's financial data.
     */
    public function manageFinancials(User $user, Shop $shop)
    {
        return $user->id === $shop->user_id || $user->user_type === 'admin';
    }

    /**
     * Determine whether the user can update the shop.
     */
    public function update(User $user, Shop $shop)
    {
        return $user->id === $shop->user_id;
    }
}
