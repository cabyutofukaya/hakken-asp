<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\ReserveInvoice;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReserveInvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any reserve_invoices.
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
            if ($appUser->isApproval('reserve_invoices', config("consts.agency_roles.READ"))) {
                return Response::allow();
            }
        }
        return Response::deny('請求書の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, ReserveInvoice $reserveInvoice)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_invoices', config("consts.agency_roles.READ")) && $reserveInvoice->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('請求書の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create reserve_invoices.
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
            if ($appUser->isApproval('reserve_invoices', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('請求書の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the reserve_invoices.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\MailTemplate  $user
     * @return mixed
     */
    public function update(AppUser $appUser, ReserveInvoice $reserveInvoice)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_invoices', config("consts.agency_roles.UPDATE")) && $reserveInvoice->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('請求書の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\MailTemplate  $mailTemplate
     * @return mixed
     */
    public function delete(AppUser $appUser, ReserveInvoice $reserveInvoice)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_invoices', config("consts.agency_roles.DELETE")) && $reserveInvoice->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('請求書の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, ReserveInvoice $reserveInvoice)
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
    public function forceDelete(AppUser $appUser, ReserveInvoice $reserveInvoice)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_invoices', config("consts.agency_roles.DELETE")) && $reserveInvoice->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('請求書の削除権限がありません(403 Forbidden)');
    }
}
