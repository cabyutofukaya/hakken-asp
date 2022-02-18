<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use App\Models\AppUser;
use App\Models\SystemNews;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class SystemNewsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any agencies.
     *
     * @param  \App\Models\AppUser  $toUser
     * @return mixed
     */
    public function viewAny(AppUser $appUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, SystemNews $systemNews)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return true;
        }
        return false;
    }

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
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(AppUser $appUser, SystemNews $systemNews)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(AppUser $appUser, SystemNews $systemNews)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, SystemNews $systemNews)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function forceDelete(AppUser $appUser, SystemNews $systemNews)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return true;
        }
        return false;

    }
}
