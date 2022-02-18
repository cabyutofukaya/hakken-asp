<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\City;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any cities.
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
            if($appUser->isApproval('cities', config("consts.agency_roles.READ"))){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, City $city)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('cities', config("consts.agency_roles.READ")) && $city->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create cities.
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
            if($appUser->isApproval('cities', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the cities.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\City  $city
     * @return mixed
     */
    public function update(AppUser $appUser, City $city)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('cities', config("consts.agency_roles.UPDATE")) && $city->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\City  $city
     * @return mixed
     */
    public function delete(AppUser $appUser, City $city)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('cities', config("consts.agency_roles.DELETE")) && $city->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\City  $city
     * @return mixed
     */
    public function restore(AppUser $appUser, City $city)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\City  $city
     * @return mixed
     */
    public function forceDelete(AppUser $appUser, City $city)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('cities', config("consts.agency_roles.DELETE")) && $city->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の削除権限がありません(403 Forbidden)');
    }
}
