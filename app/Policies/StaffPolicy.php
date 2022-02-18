<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Staff;
use App\Models\AppUser;
use App\Services\StaffService;
use App\Services\AgencyService;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class StaffPolicy
{
    use HandlesAuthorization;

    public function __construct(StaffService $staffService, AgencyService $agencyService)
    {
        $this->staffService = $staffService;
        $this->agencyService = $agencyService;
    }

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
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('staffs', config("consts.agency_roles.READ"))){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の参照権限がありません(403 Forbidden)');
    }
    
    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, Staff $staff)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('staffs', config("consts.agency_roles.READ")) && $staff->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create agencies.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param int $agencyId 会社ID
     * @return mixed
     */
    public function create(AppUser $appUser, Staff $staff, int $agencyId)
    {
        // TODO 登録可能人数未満かチェックする
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('staffs', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the agencies.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Staff  $staff
     * @return mixed
     */
    public function update(AppUser $appUser, Staff $staff)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('staffs', config("consts.agency_roles.UPDATE")) && $staff->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Agency  $agency
     * @return mixed
     */
    public function delete(AppUser $appUser, Staff $staff)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('staffs', config("consts.agency_roles.DELETE")) && $staff->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の削除権限がありません(403 Forbidden)');
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
