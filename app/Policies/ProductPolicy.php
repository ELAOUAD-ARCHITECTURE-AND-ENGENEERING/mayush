<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->user_type === 'admin') {
            return true;
        }
    }

    public function update(User $user, Product $product)
    {
        return $user->id === $product->user_id;
    }

    public function delete(User $user, Product $product)
    {
        return $user->id === $product->user_id;
    }

    public function view(User $user, Product $product)
    {
        // Public visibility logic could be handled here or in controller.
        // For strict backend viewing:
        return $user->id === $product->user_id;
    }
}
