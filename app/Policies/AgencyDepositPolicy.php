<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\AgencyDeposit;
use App\Models\AppUser;
use App\Models\User;
use App\Models\ReserveInvoice;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AgencyDepositPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any agency_deposits.
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
            if ($appUser->isApproval('agency_deposits', config("consts.agency_roles.READ"))) {
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
    public function view(AppUser $appUser, AgencyDeposit $agencyDeposit)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('agency_deposits', config("consts.agency_roles.READ")) && $agencyDeposit->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('経理業務の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create agency_deposits.
     *
     * @param  \App\Models\AppUser  $appUser
     * @return mixed
     */
    public function create(AppUser $appUser, AgencyDeposit $agencyDeposit, ReserveInvoice $reserveInvoice)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('agency_deposits', config("consts.agency_roles.CREATE")) && $reserveInvoice->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('経理業務の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the agency_deposits.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Supplier  $user
     * @return mixed
     */
    public function update(AppUser $appUser, AgencyDeposit $agencyDeposit)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('agency_deposits', config("consts.agency_roles.UPDATE")) && $agencyDeposit->agency_id == $appUser->agency_id) {
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
    public function delete(AppUser $appUser, AgencyDeposit $agencyDeposit)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('agency_deposits', config("consts.agency_roles.DELETE")) && $agencyDeposit->agency_id == $appUser->agency_id) {
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
    public function restore(AppUser $appUser, AgencyDeposit $agencyDeposit)
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
    public function forceDelete(AppUser $appUser, AgencyDeposit $agencyDeposit)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('agency_deposits', config("consts.agency_roles.DELETE")) && $agencyDeposit->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('経理業務の削除権限がありません(403 Forbidden)');
    }
}
