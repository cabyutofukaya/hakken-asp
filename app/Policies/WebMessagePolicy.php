<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\WebMessage;
use App\Models\AppUser;
use App\Models\User;
use App\Models\Reserve;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class WebMessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any web_messages.
     *
     * @param  \App\Models\AppUser  $toUser
     * @return mixed
     */
    public function viewAny(AppUser $appUser, WebMessage $webMessage, Reserve $reserve)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($reserve->agency_id !== $appUser->agency_id) {
                return Response::deny('許可されていないリクエストです(403 Forbidden)');
            }
            if ($appUser->isApproval('web_messages', config("consts.agency_roles.READ"))) {
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
    public function view(AppUser $appUser, WebMessage $webMessage)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_messages', config("consts.agency_roles.READ")) && $webMessage->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('相談履歴の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create web_messages.
     *
     * @param  \App\Models\AppUser  $appUser
     * @return mixed
     */
    public function create(AppUser $appUser, WebMessage $webMessage, Reserve $reserve)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($reserve->agency_id !== $appUser->agency_id) {
                return Response::deny('許可されていないリクエストです(403 Forbidden)');
            }
            if ($appUser->isApproval('web_messages', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('相談履歴の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the web_messages.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Supplier  $user
     * @return mixed
     */
    public function update(AppUser $appUser, WebMessage $webMessage)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_messages', config("consts.agency_roles.UPDATE")) && $webMessage->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('相談履歴の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Supplier  $supplier
     * @return mixed
     */
    public function delete(AppUser $appUser, WebMessage $webMessage)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_messages', config("consts.agency_roles.DELETE")) && $webMessage->agency_id == $appUser->agency_id) {
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
    public function restore(AppUser $appUser, WebMessage $webMessage)
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
    public function forceDelete(AppUser $appUser, WebMessage $webMessage)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('web_messages', config("consts.agency_roles.DELETE")) && $webMessage->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('相談履歴の削除権限がありません(403 Forbidden)');
    }
}
