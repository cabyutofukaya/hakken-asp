<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\AppUser;
use App\Models\User;
use App\Models\BusinessUserManager;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * 基本的にはBusinessUser(business_users)のアクセス権限があれば認可
 * 編集・削除時はログイン中ユーザーとデータ所有者の会社IDが等しいかもチェック
 */
class BusinessUserManagerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any business_user_managers.
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
            if ($appUser->isApproval('business_user_managers', config("consts.agency_roles.READ"))) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, BusinessUserManager $businessUserManager)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('business_user_managers', config("consts.agency_roles.READ")) && $businessUserManager->business_user->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create business_user_managers.
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
            if ($appUser->isApproval('business_user_managers', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the business_user_managers.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\BusinessUserManager  $user
     * @return mixed
     */
    public function update(AppUser $appUser, BusinessUserManager $businessUserManager)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('business_user_managers', config("consts.agency_roles.UPDATE")) && $businessUserManager->business_user->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\BusinessUserManager  $businessUserManager
     * @return mixed
     */
    public function delete(AppUser $appUser, BusinessUserManager $businessUserManager)
    {
        if (!$this->checkCount($businessUserManager)) {
            return Response::deny('取引先担当者は1人以上の登録が必要です。');
        }

        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('business_user_managers', config("consts.agency_roles.DELETE")) && $businessUserManager->business_user->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, BusinessUserManager $businessUserManager)
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
    public function forceDelete(AppUser $appUser, BusinessUserManager $businessUserManager)
    {
        if (!$this->checkCount($businessUserManager)) {
            return Response::deny('取引先担当者は1人以上の登録が必要です。');
        }

        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('business_user_managers', config("consts.agency_roles.DELETE")) && $businessUserManager->business_user->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(法人)の削除権限がありません(403 Forbidden)');
    }

    /**
     * 最後の一人は削除できない
     */
    private function checkCount(BusinessUserManager $businessUserManager) : bool
    {
        return $businessUserManager->business_user->business_user_managers()->count() > 1;
    }
}
