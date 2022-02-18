<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Reserve;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReservePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any reserves.
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
            if($appUser->isApproval('reserves', config("consts.agency_roles.READ"))){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, Reserve $reserve)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('reserves', config("consts.agency_roles.READ")) && $reserve->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create reserves.
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
            if($appUser->isApproval('reserves', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the reserves.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Reserve  $user
     * @return mixed
     */
    public function update(AppUser $appUser, Reserve $reserve)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('reserves', config("consts.agency_roles.UPDATE")) && $reserve->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Reserve  $reserve
     * @return mixed
     */
    public function delete(AppUser $appUser, Reserve $reserve)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('reserves', config("consts.agency_roles.DELETE")) && $reserve->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, Reserve $reserve)
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
    public function forceDelete(AppUser $appUser, Reserve $reserve)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('reserves', config("consts.agency_roles.DELETE")) && $reserve->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の削除権限がありません(403 Forbidden)');
    }
}
