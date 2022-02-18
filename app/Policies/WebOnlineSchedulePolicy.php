<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\WebOnlineSchedule;
use App\Models\AppUser;
use App\Models\User;
use App\Services\WebReserveExtService;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class WebOnlineSchedulePolicy
{
    use HandlesAuthorization;

    public function __construct(WebReserveExtService $webReserveExtService)
    {
        $this->webReserveExtService = $webReserveExtService;
    }

    /**
     * Determine whether the user can view any web_online_schedules.
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
            if ($appUser->isApproval('web_online_schedules', config("consts.agency_roles.READ"))) {
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
    public function view(AppUser $appUser, WebOnlineSchedule $webOnlineSchedule)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_online_schedules', config("consts.agency_roles.READ")) && $webOnlineSchedule->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create web_online_schedules.
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
            if ($appUser->isApproval('web_online_schedules', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the web_online_schedules.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\WebOnlineSchedule  $user
     * @return mixed
     */
    public function update(AppUser $appUser, WebOnlineSchedule $webOnlineSchedule)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_online_schedules', config("consts.agency_roles.UPDATE")) && $webOnlineSchedule->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the web_online_schedules.
     * リクエスト更新
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\WebOnlineSchedule  $user
     * @return mixed
     */
    public function changeRequest(AppUser $appUser, WebOnlineSchedule $webOnlineSchedule)
    {
        $webReserveExt = $this->webReserveExtService->find($webOnlineSchedule->web_reserve_ext_id);

        // 辞退済の場合はエラー
        if ($webReserveExt->rejection_at) {
            return Response::deny('辞退済のリクエストです(403 Forbidden)');
        }

        // ユーザーによる取り消し済の場合はエラー
        if (optional($webReserveExt->web_consult)->cancel_at) {
            return Response::deny('ユーザーによる取り消し済み案件です(403 Forbidden)');
        }

        // 会社側からの変更依頼中の場合はエラー
        if ($webOnlineSchedule->requester == config('consts.web_online_schedules.SENDER_TYPE_CLIENT') && $webOnlineSchedule->request_status ==config('consts.web_online_schedules.ONLINE_REQUEST_STATUS_CHANGE')) {
            return Response::deny('日時変更依頼中です(403 Forbidden)');
        }

        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_online_schedules', config("consts.agency_roles.UPDATE")) && $webOnlineSchedule->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }


    /**
     * Determine whether the user can update the web_online_schedules.
     * リクエスト承諾
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\WebOnlineSchedule  $user
     * @return mixed
     */
    public function consentRequest(AppUser $appUser, WebOnlineSchedule $webOnlineSchedule)
    {
        $webReserveExt = $this->webReserveExtService->find($webOnlineSchedule->web_reserve_ext_id);

        // 辞退済の場合はエラー
        if ($webReserveExt->rejection_at) {
            return Response::deny('辞退済のリクエストです(403 Forbidden)');
        }

        // ユーザーによる取り消し済の場合はエラー
        if (optional($webReserveExt->web_consult)->cancel_at) {
            return Response::deny('ユーザーによる取り消し済み案件です(403 Forbidden)');
        }

        // 依頼者がhakkenユーザーでない場合はエラー
        if ($webOnlineSchedule->requester != config('consts.web_online_schedules.SENDER_TYPE_USER')) {
            return Response::deny('ユーザーからの変更リクエストではありません(403 Forbidden)');
        }

        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_online_schedules', config("consts.agency_roles.UPDATE")) && $webOnlineSchedule->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\WebOnlineSchedule  $webOnlineSchedule
     * @return mixed
     */
    public function delete(AppUser $appUser, WebOnlineSchedule $webOnlineSchedule)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_online_schedules', config("consts.agency_roles.DELETE")) && $webOnlineSchedule->agency_id == $appUser->agency_id) {
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
