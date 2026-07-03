<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Upload;
use Illuminate\Auth\Access\HandlesAuthorization;

class UploadPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->user_type === 'admin') {
            return true;
        }
    }

    public function view(User $user, Upload $upload)
    {
        return $user->id === $upload->user_id;
    }

    public function delete(User $user, Upload $upload)
    {
        return $user->id === $upload->user_id;
    }

    public function restore(User $user, Upload $upload)
    {
        return $user->id === $upload->user_id;
    }

    public function forceDelete(User $user, Upload $upload)
    {
        return $user->id === $upload->user_id;
    }
}
