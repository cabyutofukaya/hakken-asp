<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\AgencyConsultation;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AgencyConsultationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any agency_consultations.
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
            if($appUser->isApproval('agency_consultations', config("consts.agency_roles.READ"))){
                return Response::allow();
            }
        }
        return Response::deny('相談履歴の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, AgencyConsultation $agencyConsultation)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('agency_consultations', config("consts.agency_roles.READ")) && $agencyConsultation->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('相談履歴の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create agency_consultations.
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
            if($appUser->isApproval('agency_consultations', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('相談履歴の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the agency_consultations.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\AgencyConsultation  $user
     * @return mixed
     */
    public function update(AppUser $appUser, AgencyConsultation $agencyConsultation)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('agency_consultations', config("consts.agency_roles.UPDATE")) && $agencyConsultation->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('相談履歴の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\AgencyConsultation  $agencyConsultation
     * @return mixed
     */
    public function delete(AppUser $appUser, AgencyConsultation $agencyConsultation)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('agency_consultations', config("consts.agency_roles.DELETE")) && $agencyConsultation->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('相談履歴の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, AgencyConsultation $agencyConsultation)
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
    public function forceDelete(AppUser $appUser, AgencyConsultation $agencyConsultation)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('agency_consultations', config("consts.agency_roles.DELETE")) && $agencyConsultation->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('相談履歴の削除権限がありません(403 Forbidden)');
    }
}
