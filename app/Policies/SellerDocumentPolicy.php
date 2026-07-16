<?php

namespace App\Policies;

use App\Models\SellerDocument;
use App\Models\User;

class SellerDocumentPolicy
{
    public function view(User $user, SellerDocument $document): bool
    {
        return in_array($user->user_type, ['admin', 'staff'], true)
            || ($user->user_type === 'seller'
                && !$user->banned
                && (int) $document->shop?->user_id === (int) $user->id);
    }
}
