<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use App\Models\AppUser;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
            return Response::allow();
        } else if ($model === 'Staff') {
            // リクエストURLと当該スタッフが所属している会社との生合成はCheckAgencyAccountミドルウェアにてチェック済
            if($appUser->isApproval('users', config("consts.agency_roles.READ"))){
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, User $user)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('users', config("consts.agency_roles.READ")) && $user->agency_id == $user->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  \App\Models\AppUser  $appUser
     * @return mixed
     */
    public function create(AppUser $appUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('users', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(AppUser $appUser, User $user)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('users', config("consts.agency_roles.UPDATE")) && $user->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\User  $user
     * @return mixed
     */
    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(AppUser $appUser, User $user)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('users', config("consts.agency_roles.DELETE")) && $user->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, User $user)
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
    public function forceDelete(AppUser $appUser, User $user)
    {
        //
    }
}
