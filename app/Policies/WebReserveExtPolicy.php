<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\AppUser;
use App\Models\User;
use App\Models\WebReserveExt;
use App\Services\WebReserveExtService;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class WebReserveExtPolicy
{
    use HandlesAuthorization;

    public function __construct(WebReserveExtService $webReserveExtService)
    {
        $this->webReserveExtService = $webReserveExtService;
    }

    /**
     * Determine whether the user can view any web_reserve_exts.
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
            if ($appUser->isApproval('web_reserve_exts', config("consts.agency_roles.READ"))) {
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
    public function view(AppUser $appUser, WebReserveExt $webReserveExt)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_reserve_exts', config("consts.agency_roles.READ")) && $webReserveExt->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create web_reserve_exts.
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
            if ($appUser->isApproval('web_reserve_exts', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the web_reserve_exts.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Supplier  $user
     * @return mixed
     */
    public function update(AppUser $appUser, WebReserveExt $webReserveExt)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_reserve_exts', config("consts.agency_roles.UPDATE")) && $webReserveExt->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の更新権限がありません(403 Forbidden)');
    }

    /**
     * 辞退
     */
    public function reject(AppUser $appUser, WebReserveExt $webReserveExt)
    {
        // 承諾済の場合はエラー
        if ($webReserveExt->consent_at) {
            return Response::deny('受付済のリクエストです(403 Forbidden)');
        }

        // 辞退済の場合はエラー
        if ($webReserveExt->rejection_at) {
            return Response::deny('辞退済のリクエストです(403 Forbidden)');
        }

        // ユーザーによる取り消し済の場合はエラー
        if ($webReserveExt->web_consult->cancel_at) {
            return Response::deny('ユーザーによる取り消し済み案件です(403 Forbidden)');
        }

        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($webReserveExt->manager_id !== auth('staff')->user()->id) {
                return Response::deny('辞退処理は担当マイスターのみ可能です(403 Forbidden)');
            }
            if ($appUser->isApproval('web_reserve_exts', config("consts.agency_roles.UPDATE")) && $webReserveExt->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

    /**
     * Web相談を受付
     */
    public function consent(AppUser $appUser, WebReserveExt $webReserveExt)
    {
        // 受付上限値を超えている
        if ($webReserveExt->web_consult->is_reach_consult_max()) {
            return Response::deny('受付数上限に達したため受付を締め切りました。');
        }
        
        // 承諾済の場合はエラー
        if ($webReserveExt->consent_at) {
            return Response::deny('受付済のリクエストです(403 Forbidden)');
        }

        // 辞退済の場合はエラー
        if ($webReserveExt->rejection_at) {
            return Response::deny('辞退済のリクエストです(403 Forbidden)');
        }

        // ユーザーによる取り消し済の場合はエラー
        if (optional($webReserveExt->web_consult)->cancel_at) {
            return Response::deny('ユーザーによる取り消し済み案件です(403 Forbidden)');
        }


        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($webReserveExt->manager_id !== auth('staff')->user()->id) {
                return Response::deny('受付処理は担当マイスターのみ可能です(403 Forbidden)');
            }
            if ($appUser->isApproval('web_reserve_exts', config("consts.agency_roles.UPDATE")) && $webReserveExt->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('許可されていないリクエストです(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\WebReserveExt  $webReserveExt
     * @return mixed
     */
    public function delete(AppUser $appUser, WebReserveExt $webReserveExt)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_reserve_exts', config("consts.agency_roles.DELETE")) && $webReserveExt->agency_id == $appUser->agency_id) {
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
    public function restore(AppUser $appUser, WebReserveExt $webReserveExt)
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
    public function forceDelete(AppUser $appUser, WebReserveExt $webReserveExt)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_reserve_exts', config("consts.agency_roles.DELETE")) && $webReserveExt->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の削除権限がありません(403 Forbidden)');
    }
}
