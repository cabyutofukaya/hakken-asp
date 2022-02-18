<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\AppUser;
use App\Models\User;
use App\Models\UserMemberCard;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * 基本的にはUser(users)のアクセス権限があれば認可
 * 編集・削除時はログイン中ユーザーとデータ所有者の会社IDが等しいかもチェック
 */
class UserMemberCardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any user_member_cards.
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
            if ($appUser->isApproval('user_member_cards', config("consts.agency_roles.READ"))) {
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
    public function view(AppUser $appUser, UserMemberCard $userMemberCard)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_member_cards', config("consts.agency_roles.READ")) && $userMemberCard->user->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create user_member_cards.
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
            if ($appUser->isApproval('user_member_cards', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the user_member_cards.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\UserMemberCard  $user
     * @return mixed
     */
    public function update(AppUser $appUser, UserMemberCard $userMemberCard)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_member_cards', config("consts.agency_roles.UPDATE")) && $userMemberCard->user->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\UserMemberCard  $userMemberCard
     * @return mixed
     */
    public function delete(AppUser $appUser, UserMemberCard $userMemberCard)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_member_cards', config("consts.agency_roles.DELETE")) && $userMemberCard->user->agency_id == $appUser->agency_id) {
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
    public function restore(AppUser $appUser, UserMemberCard $userMemberCard)
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
    public function forceDelete(AppUser $appUser, UserMemberCard $userMemberCard)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('user_member_cards', config("consts.agency_roles.DELETE")) && $userMemberCard->user->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('顧客管理(個人)の削除権限がありません(403 Forbidden)');
    }
}
