<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\SubjectHotel;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SubjectHotelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any subject_hotels.
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
            if($appUser->isApproval('subject_hotels', config("consts.agency_roles.READ"))){
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
    public function view(AppUser $appUser, SubjectHotel $subjectHotel)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('subject_hotels', config("consts.agency_roles.READ")) && $subjectHotel->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create subject_hotels.
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
            if($appUser->isApproval('subject_hotels', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the subject_hotels.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Supplier  $user
     * @return mixed
     */
    public function update(AppUser $appUser, SubjectHotel $subjectHotel)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('subject_hotels', config("consts.agency_roles.UPDATE")) && $subjectHotel->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Supplier  $supplier
     * @return mixed
     */
    public function delete(AppUser $appUser, SubjectHotel $subjectHotel)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('subject_hotels', config("consts.agency_roles.DELETE")) && $subjectHotel->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, SubjectHotel $subjectHotel)
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
    public function forceDelete(AppUser $appUser, SubjectHotel $subjectHotel)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('subject_hotels', config("consts.agency_roles.DELETE")) && $subjectHotel->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の削除権限がありません(403 Forbidden)');
    }
}
