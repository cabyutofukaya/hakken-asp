<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Participant;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ParticipantPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any participants.
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
            if($appUser->isApproval('participants', config("consts.agency_roles.READ"))){
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
    public function view(AppUser $appUser, Participant $participant)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('participants', config("consts.agency_roles.READ")) && $participant->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create participants.
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
            if($appUser->isApproval('participants', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the participants.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Participant  $participant
     * @return mixed
     */
    public function update(AppUser $appUser, Participant $participant)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('participants', config("consts.agency_roles.UPDATE")) && $participant->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の更新権限がありません(403 Forbidden)');
    }

    // 代表者設定
    public function representative(AppUser $appUser, Participant $participant)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            if (!$participant->cancel) { // 取消ユーザーではないこと
                return Response::allow();
            }
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('participants', config("consts.agency_roles.UPDATE")) && $participant->agency_id == $appUser->agency_id && !$participant->cancel){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の代表者設定権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Participant  $participant
     * @return mixed
     */
    public function delete(AppUser $appUser, Participant $participant)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('participants', config("consts.agency_roles.DELETE")) && $participant->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Participant  $participant
     * @return mixed
     */
    public function restore(AppUser $appUser, Participant $participant)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Participant  $participant
     * @return mixed
     */
    public function forceDelete(AppUser $appUser, Participant $participant)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('participants', config("consts.agency_roles.DELETE")) && $participant->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の削除権限がありません(403 Forbidden)');
    }
}
