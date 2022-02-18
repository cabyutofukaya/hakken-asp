<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\AppUser;
use App\Models\User;
use App\Models\UserVisa;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * 編集・削除時はログイン中ユーザーとデータ所有者の会社IDが等しいかもチェック
 */
class UserVisaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any user_visas.
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
            if ($appUser->isApproval('user_visas', config("consts.agency_roles.READ"))) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, UserVisa $userVisa)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_visas', config("consts.agency_roles.READ")) && $userVisa->user->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create user_visas.
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
            if ($appUser->isApproval('user_visas', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the user_visas.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\UserVisa  $user
     * @return mixed
     */
    public function update(AppUser $appUser, UserVisa $userVisa)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_visas', config("consts.agency_roles.UPDATE")) && $userVisa->user->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\UserVisa  $userVisa
     * @return mixed
     */
    public function delete(AppUser $appUser, UserVisa $userVisa)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_visas', config("consts.agency_roles.DELETE")) && $userVisa->user->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, UserVisa $userVisa)
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
    public function forceDelete(AppUser $appUser, UserVisa $userVisa)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_visas', config("consts.agency_roles.DELETE")) && $userVisa->user->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の削除権限がありません(403 Forbidden)');
    }
}
