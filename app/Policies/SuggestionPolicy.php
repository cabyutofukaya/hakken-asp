<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Agency;
use App\Models\AppUser;
use App\Models\Suggestion;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuggestionPolicy
{
    use HandlesAuthorization;

    // /**
    //  * Determine whether the user can view any agencies.
    //  *
    //  * @param  \App\Models\AppUser  $toUser
    //  * @return mixed
    //  */
    // public function viewAny(AppUser $appUser, Suggestion $suggestion)
    // {
    //     $result = false;

    //     $model = class_basename(get_class($appUser));
    //     if ($model === 'Admin') {
    //         $result = true;
    //     } elseif ($model === 'Agency') {
    //         $result = $appUser->id === $suggestion->staff->agency->id;
    //     } elseif ($model==='User') {
    //         $result = $appUser->id === $suggestion->user_id;
    //     }
    //     return $result ? Response::allow() : Response::deny('許可されていないリクエストです(403 Forbidden)');
    // }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, Suggestion $suggestion)
    {
        $result = false;

        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            $result = true;
        } elseif ($model === 'Agency') {
            $result = $appUser->id === $suggestion->staff->agency->id;
        } elseif ($model==='User') {
            $result = $appUser->id === $suggestion->user_id;
        }
        return $result ? Response::allow() : Response::deny('許可されていないリクエストです(403 Forbidden)');

    }

    // /**
    //  * Determine whether the user can create agencies.
    //  *
    //  * @param  \App\Models\AppUser  $appUser
    //  * @return mixed
    //  */
    // public function create(AppUser $appUser)
    // {
    //     $model = class_basename(get_class($appUser));
    //     if ($model === 'Admin') {
    //         return true;
    //     }
    //     return false;
    // }

    // /**
    //  * Determine whether the user can update the agencies.
    //  *
    //  * @param  \App\Models\AppUser  $appUser
    //  * @param  \App\Agency  $agency
    //  * @return mixed
    //  */
    // public function update(AppUser $appUser, Agency $agency)
    // {
    //     $model = class_basename(get_class($appUser));
    //     if ($model === 'Admin') {
    //         return true;
    //     } elseif ($model === 'Agency') {
    //         return $appUser->id === $agency->id;
    //     }
    //     return false;
    // }

    // /**
    //  * Determine whether the user can delete the user.
    //  *
    //  * @param  \App\Models\AppUser  $appUser
    //  * @param  \App\Agency  $agency
    //  * @return mixed
    //  */
    // public function delete(AppUser $appUser, Agency $agency)
    // {
    //     $model = class_basename(get_class($appUser));
    //     if ($model === 'Admin') {
    //         return true;
    //     } elseif ($model === 'Agency') {
    //         return $appUser->id === $agency->id;
    //     }
    //     return false;
    // }

    // /**
    //  * Determine whether the user can restore the user.
    //  *
    //  * @param  \App\Models\AppUser  $user
    //  * @param  \App\Ugency  $user
    //  * @return mixed
    //  */
    // public function restore(AppUser $appUser, User $user)
    // {
    //     //
    // }

    // /**
    //  * Determine whether the user can permanently delete the user.
    //  *
    //  * @param  \App\Models\AppUser  $user
    //  * @param  \App\User  $user
    //  * @return mixed
    //  */
    // public function forceDelete(AppUser $appUser, User $user)
    // {
    //     //
    // }
}
