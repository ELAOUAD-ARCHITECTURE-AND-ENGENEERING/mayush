<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SystemLogPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->user_type === 'admin') {
            return true;
        }
    }

    public function viewPaymentLogs(User $user)
    {
        return $user->user_type === 'staff' && $user->can('manage_payments');
    }

    public function viewShipmentLogs(User $user)
    {
        return $user->user_type === 'staff' && $user->can('view_system_logs');
    }
}
