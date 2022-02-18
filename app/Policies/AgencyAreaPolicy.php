<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\AgencyArea;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AgencyAreaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any areas.
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

            /* areas,v_areas両方の閲覧権限があれば閲覧可 */
            
            if ($appUser->isApproval('v_areas', config("consts.agency_roles.READ")) && $appUser->isApproval('areas', config("consts.agency_roles.READ"))) {
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
    public function view(AppUser $appUser, AgencyArea $agencyArea)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {

            /* 対象クラスがVAreaかAreaかでチェックを分ける */
            
            if (is_a($agencyArea, 'App\Models\VArea')) {
                if ($appUser->isApproval('v_areas', config("consts.agency_roles.READ")) && ($agencyArea->agency_id === config('consts.const.MASTER_AGENCY_ID') || $agencyArea->agency_id === $appUser->agency_id)) { // agency_id=0（マスターレコード）の値は全社閲覧可
                    return Response::allow();
                }
            } elseif (is_a($agencyArea, 'App\Models\Area')) {
                if ($appUser->isApproval('areas', config("consts.agency_roles.READ")) && $agencyArea->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            }

        }
        return Response::deny('マスタ管理の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create areas.
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

            /* v_areasに対する新規登録はできないので、areasの権限のみチェック */

            if($appUser->isApproval('areas', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('マスタ管理の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the areas.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Area  $user
     * @return mixed
     */
    public function update(AppUser $appUser, AgencyArea $agencyArea)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {

            /* v_areasに対する更新はできないので、areasの権限で判定 */

            if (is_a($agencyArea, 'App\Models\VArea')) {
                if ($appUser->isApproval('areas', config("consts.agency_roles.UPDATE")) && $agencyArea->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            } elseif (is_a($agencyArea, 'App\Models\Area')) {
                if ($appUser->isApproval('areas', config("consts.agency_roles.UPDATE")) && $agencyArea->agency_id === $appUser->agency_id) {
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
     * @param  \App\Area  $area
     * @return mixed
     */
    public function delete(AppUser $appUser, AgencyArea $agencyArea)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {

            /* v_areasに対する削除はできないので、areasの権限で判定 */

            if (is_a($agencyArea, 'App\Models\VArea')) {
                if ($appUser->isApproval('areas', config("consts.agency_roles.DELETE")) && $agencyArea->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            } elseif (is_a($agencyArea, 'App\Models\Area')) {
                if ($appUser->isApproval('areas', config("consts.agency_roles.DELETE")) && $agencyArea->agency_id === $appUser->agency_id) {
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
    public function restore(AppUser $appUser, AgencyArea $agencyArea)
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
    public function forceDelete(AppUser $appUser, AgencyArea $agencyArea)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            
            /* v_areasに対する削除はできないので、areasの権限で判定 */

            if (is_a($agencyArea, 'App\Models\VArea')) {
                if ($appUser->isApproval('areas', config("consts.agency_roles.DELETE")) && $agencyArea->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            } elseif (is_a($agencyArea, 'App\Models\Area')) {
                if ($appUser->isApproval('areas', config("consts.agency_roles.DELETE")) && $agencyArea->agency_id === $appUser->agency_id) {
                    return Response::allow();
                }
            }

        }
        return Response::deny('マスタ管理の削除権限がありません(403 Forbidden)');
    }
}
