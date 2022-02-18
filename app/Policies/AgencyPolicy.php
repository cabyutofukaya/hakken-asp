<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Agency;
use App\Models\AppUser;
use App\Services\StaffService;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgencyPolicy
{
    use HandlesAuthorization;

    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    // /**
    //  * Determine whether the user can view any agencies.
    //  *
    //  * @param  \App\Models\AppUser  $toUser
    //  * @return mixed
    //  */
    // public function viewAny(AppUser $appUser)
    // {
    //     $model = class_basename(get_class($appUser));
    //     if ($model === 'Admin') {
    //         return true;
    //     }
    //     return false;
    // }

    // /**
    //  * Determine whether the user can view the agencies.
    //  *
    //  * @param  \App\Models\AppUser  $user
    //  * @param  \App\User  $user
    //  * @return mixed
    //  */
    // public function view(AppUser $appUser, User $user)
    // {
    //     $model = class_basename(get_class($appUser));
    //     if ($model === 'Admin') {
    //         return true;
    //     }
    //     return false;
    // }

    /**
     * Determine whether the user can create agencies.
     *
     * @param  \App\Models\AppUser  $appUser
     * @return mixed
     */
    public function create(AppUser $appUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the agencies.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Agency  $agency
     * @return mixed
     */
    public function updateStatus(AppUser $appUser, Agency $agency)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        }
    
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the agencies.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Agency  $agency
     * @return mixed
     */
    public function update(AppUser $appUser, Agency $agency, int $staffNumber)
    {
        $result = false;

        // 登録可能人数未満かチェック
        $diffNumber = $this->staffService->countByAgencyId($agency->id) - $staffNumber;
        if ($diffNumber > 0) {
            return Response::deny(sprintf("「スタッフ登録許可数」が減っています。スタッフアカウントを%dつ削除してください。", $diffNumber));
        } else {
            $model = class_basename(get_class($appUser));
            if ($model === 'Admin') {
                return Response::allow();
            } elseif ($model==='Agency') {
                return $appUser->id === $agency->id ? Response::allow() : Response::deny('許可されていないリクエストです(403 Forbidden)');
            }
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Agency  $agency
     * @return mixed
     */
    public function delete(AppUser $appUser, Agency $agency)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Agency') {
            if ($appUser->id === $agency->id) {
                return Response::allow();
            }
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

    public function isAccountExists(AppUser $appUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

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
