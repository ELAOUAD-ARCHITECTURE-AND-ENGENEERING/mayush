<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order)
    {
        // Customer who placed the order
        if ($user->id === $order->user_id) {
            return true;
        }

        // Seller who owns the shop for this order
        if ($user->id === $order->seller_id) {
            return true;
        }

        // Delivery boy assigned to the order
        if ($user->id === $order->assign_delivery_boy) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order)
    {
        // Only Seller or Admin can update status
        return $user->id === $order->seller_id || $user->user_type === 'admin';
    }
}
