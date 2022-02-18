<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\UserCustomItem;
use App\Models\AppUser;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserCustomItemPolicy
{
    use HandlesAuthorization;

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
            if ($appUser->isApproval('user_custom_items', config("consts.agency_roles.READ"))) {
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
    public function view(AppUser $appUser, UserCustomItem $userCustomItem)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_custom_items', config("consts.agency_roles.READ")) && $userCustomItem->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('システム設定の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create user_custom_items.
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
            if ($appUser->isApproval('user_custom_items', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('システム設定の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create user_custom_items.
     *
     * @param  \App\Models\AppUser  $appUser
     * @return mixed
     */
    public function store(AppUser $appUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_custom_items', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('システム設定の登録権限がありません(403 Forbidden)');
    }
    /**
     * Determine whether the user can update the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(AppUser $appUser, UserCustomItem $userCustomItem)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_custom_items', config("consts.agency_roles.UPDATE")) && $userCustomItem->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('システム設定の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(AppUser $appUser, UserCustomItem $userCustomItem)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_custom_items', config("consts.agency_roles.DELETE")) && $userCustomItem->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('システム設定の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, UserCustomItem $userCustomItem)
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
    public function forceDelete(AppUser $appUser, UserCustomItem $userCustomItem)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_custom_items', config("consts.agency_roles.DELETE")) && $userCustomItem->agency_id == $appUser->agency_id && !$userCustomItem->undelete_item) {
                return Response::allow();
            }
        }
        return Response::deny('システム設定の削除権限がありません(403 Forbidden)');
    }
}
