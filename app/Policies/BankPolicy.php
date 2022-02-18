<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Bank;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class BankPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any banks.
     *
     * @param  \App\Models\AppUser  $toUser
     * @return mixed
     */
    public function viewAny(AppUser $appUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, Bank $bank)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

    /**
     * Determine whether the user can create banks.
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
     * Determine whether the user can update the banks.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Bank  $user
     * @return mixed
     */
    public function update(AppUser $appUser, Bank $bank)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Bank  $bank
     * @return mixed
     */
    public function delete(AppUser $appUser, Bank $bank)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, Bank $bank)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function forceDelete(AppUser $appUser, Bank $bank)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }
}
