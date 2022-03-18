<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\ReserveReceipt;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReserveReceiptPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any reserve_receipts.
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
            if ($appUser->isApproval('reserve_receipts', config("consts.agency_roles.READ"))) {
                return Response::allow();
            }
        }
        return Response::deny('領収書の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, ReserveReceipt $reserveReceipt)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_receipts', config("consts.agency_roles.READ")) && $reserveReceipt->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('領収書の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create reserve_receipts.
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
            if ($appUser->isApproval('reserve_receipts', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('領収書の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the reserve_receipts.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\MailTemplate  $user
     * @return mixed
     */
    public function update(AppUser $appUser, ReserveReceipt $reserveReceipt)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_receipts', config("consts.agency_roles.UPDATE")) && $reserveReceipt->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('領収書の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\MailTemplate  $mailTemplate
     * @return mixed
     */
    public function delete(AppUser $appUser, ReserveReceipt $reserveReceipt)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_receipts', config("consts.agency_roles.DELETE")) && $reserveReceipt->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('領収書の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, ReserveReceipt $reserveReceipt)
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
    public function forceDelete(AppUser $appUser, ReserveReceipt $reserveReceipt)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_receipts', config("consts.agency_roles.DELETE")) && $reserveReceipt->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('領収書の削除権限がありません(403 Forbidden)');
    }
}
