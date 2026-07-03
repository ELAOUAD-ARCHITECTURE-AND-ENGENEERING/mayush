<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RefundRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefundPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->user_type === 'admin') {
            return true;
        }
    }

    public function view(User $user, RefundRequest $refundRequest)
    {
        if ($user->id === $refundRequest->user_id) {
            return true;
        }

        if ($user->user_type === 'seller' && $user->id === $refundRequest->seller_id) {
            return true;
        }

        return false;
    }

    public function update(User $user, RefundRequest $refundRequest)
    {
        return $user->id === $refundRequest->user_id;
    }
}
