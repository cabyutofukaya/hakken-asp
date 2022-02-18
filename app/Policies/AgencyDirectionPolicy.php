<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\VDirection;
use App\Models\AgencyDirection;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * DirectionとVDirection用ポリシー(Agency向け)
 */
class AgencyDirectionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any v_directions.
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

            /* directions,v_directions両方の閲覧権限があれば閲覧可 */
            
            if ($appUser->isApproval('v_directions', config("consts.agency_roles.READ")) && $appUser->isApproval('directions', config("consts.agency_roles.READ"))) {
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, AgencyDirection $agencyDirection)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {

            /* 対象クラスがVDirectionかDirectionかでチェックを分ける */
            
            if (is_a($agencyDirection, 'App\Models\VDirection')) {
                if ($appUser->isApproval('v_directions', config("consts.agency_roles.READ")) && ($agencyDirection->agency_id === config('consts.const.MASTER_AGENCY_ID') || $agencyDirection->agency_id === $appUser->agency_id)) { // agency_id=0（マスターレコード）の値は全社閲覧可
                    return Response::allow();
                }
            } elseif (is_a($agencyDirection, 'App\Models\Direction')) {
                if ($appUser->isApproval('directions', config("consts.agency_roles.READ")) && $agencyDirection->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            }
        }
        return Response::deny('マスタ管理の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create directions.
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

            /* v_directionsに対する新規登録はできないので、directionsの権限のみチェック */
            
            if ($appUser->isApproval('directions', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the directions.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Direction  $user
     * @return mixed
     */
    public function update(AppUser $appUser, AgencyDirection $agencyDirection)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {

             /* v_directionsに対する更新はできないので、directionsの権限で判定 */

            if (is_a($agencyDirection, 'App\Models\VDirection')) {
                if ($appUser->isApproval('directions', config("consts.agency_roles.UPDATE")) && $agencyDirection->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            } elseif (is_a($agencyDirection, 'App\Models\Direction')) {
                if ($appUser->isApproval('directions', config("consts.agency_roles.UPDATE")) && $agencyDirection->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            }
        }
        return Response::deny('マスタ管理の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\Direction  $direction
     * @return mixed
     */
    public function delete(AppUser $appUser, AgencyDirection $agencyDirection)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {

            /* v_directionsに対する削除はできないので、directionsの権限で判定 */

            if (is_a($agencyDirection, 'App\Models\VDirection')) {
                if ($appUser->isApproval('directions', config("consts.agency_roles.DELETE")) && $agencyDirection->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            } elseif (is_a($agencyDirection, 'App\Models\Direction')) {
                if ($appUser->isApproval('directions', config("consts.agency_roles.DELETE")) && $agencyDirection->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            }

        }
        return Response::deny('マスタ管理の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, AgencyDirection $agencyDirection)
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
    public function forceDelete(AppUser $appUser, AgencyDirection $agencyDirection)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {

             /* v_directionsに対する更新はできないので、directionsの権限で判定 */

            if (is_a($agencyDirection, 'App\Models\VDirection')) {
                if ($appUser->isApproval('directions', config("consts.agency_roles.DELETE")) && $agencyDirection->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            } elseif (is_a($agencyDirection, 'App\Models\Direction')) {
                if ($appUser->isApproval('directions', config("consts.agency_roles.DELETE")) && $agencyDirection->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            }

        }
        return Response::deny('マスタ管理の削除権限がありません(403 Forbidden)');
    }
}
