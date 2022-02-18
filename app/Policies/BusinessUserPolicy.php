<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\BusinessUser;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class BusinessUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any business_users.
     *
     * @param  \App\Models\AppUser  $toUser
     * @return mixed
     */
    public function viewAny(AppUser $appUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('business_users', config("consts.agency_roles.READ"))){
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, BusinessUser $businessUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('business_users', config("consts.agency_roles.READ")) && $businessUser->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create business_users.
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
            if($appUser->isApproval('business_users', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the business_users.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\BusinessUser  $businessUser
     * @return mixed
     */
    public function update(AppUser $appUser, BusinessUser $businessUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('business_users', config("consts.agency_roles.UPDATE")) && $businessUser->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\BusinessUser  $businessUser
     * @return mixed
     */
    public function delete(AppUser $appUser, BusinessUser $businessUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('business_users', config("consts.agency_roles.DELETE")) && $businessUser->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\BusinessUser  $businessUser
     * @return mixed
     */
    public function restore(AppUser $appUser, BusinessUser $businessUser)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\BusinessUser  $businessUser
     * @return mixed
     */
    public function forceDelete(AppUser $appUser, BusinessUser $businessUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('business_users', config("consts.agency_roles.DELETE")) && $businessUser->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の削除権限がありません(403 Forbidden)');
    }
}
