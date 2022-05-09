<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\AccountPayableItem;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AccountPayableItemPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any account_payable_items.
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
            if($appUser->isApproval('account_payable_items', config("consts.agency_roles.READ"))){
                return Response::allow();
            }
        }
        return Response::deny('経理業務の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, AccountPayableItem $accountPayableItem)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('account_payable_items', config("consts.agency_roles.READ")) && $accountPayableItem->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('経理業務の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create account_payable_items.
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
            if($appUser->isApproval('account_payable_items', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('経理業務の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the account_payable_items.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Supplier  $user
     * @return mixed
     */
    public function update(AppUser $appUser, AccountPayableItem $accountPayableItem)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('account_payable_items', config("consts.agency_roles.UPDATE")) && $accountPayableItem->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('経理業務の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Supplier  $supplier
     * @return mixed
     */
    public function delete(AppUser $appUser, AccountPayableItem $accountPayableItem)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('account_payable_items', config("consts.agency_roles.DELETE")) && $accountPayableItem->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('経理業務の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, AccountPayableItem $accountPayableItem)
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
    public function forceDelete(AppUser $appUser, AccountPayableItem $accountPayableItem)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('account_payable_items', config("consts.agency_roles.DELETE")) && $accountPayableItem->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('経理業務の削除権限がありません(403 Forbidden)');
    }
}
