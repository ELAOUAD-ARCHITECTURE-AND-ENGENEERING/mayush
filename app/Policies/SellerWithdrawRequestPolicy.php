<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SellerWithdrawRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class SellerWithdrawRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the withdraw request.
     */
    public function view(User $user, SellerWithdrawRequest $request)
    {
        return $user->id === $request->user_id || $user->user_type === 'admin';
    }
}
