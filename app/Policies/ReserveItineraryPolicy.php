<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Reserve;
use App\Models\ReserveItinerary;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReserveItineraryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any reserve_itineraries.
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
            if ($appUser->isApproval('reserve_itineraries', config("consts.agency_roles.READ"))) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, ReserveItinerary $reserveItinerary)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_itineraries', config("consts.agency_roles.READ")) && $reserveItinerary->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create reserve_itineraries.
     *
     * @param  \App\Models\AppUser  $appUser
     * @return mixed
     */
    public function create(AppUser $appUser, ReserveItinerary $reserveItinerary, Reserve $reserve)
    {
        if ($reserve->application_step == config("consts.reserves.APPLICATION_STEP_RESERVE") && $reserve->reserve_itineraries->where('enabled', true)->count() > 0) { // 予約状態且つ、有効行程がすでにあれば追加作成不可
            return Response::deny('有効行程は作成済みです。予約情報ページにて最新情報をご確認ください(403 Forbidden)');
        }

        if ($reserve->is_canceled) {
            return Response::deny('キャンセル済みの予約は行程の編集ができません(403 Forbidden)'); // キャンセル済み予約で行程を作成・編集できると経理の計算がおかしくなるのでとりあえず不許可
        }

        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_itineraries', config("consts.agency_roles.CREATE"))) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the reserve_itineraries.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\ReserveItinerary  $reserveItinerary
     * @return mixed
     */
    public function update(AppUser $appUser, ReserveItinerary $reserveItinerary)
    {
        if ($reserveItinerary->reserve->is_canceled) {
            return Response::deny('キャンセル済みの予約は行程の編集ができません(403 Forbidden)'); // キャンセル済み予約で行程を作成・編集できると経理の計算がおかしくなるのでとりあえず不許可
        }

        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_itineraries', config("consts.agency_roles.UPDATE")) && $reserveItinerary->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\ReserveItinerary  $reserveItinerary
     * @return mixed
     */
    public function delete(AppUser $appUser, ReserveItinerary $reserveItinerary)
    {
        // 予約状態の行程は削除不可
        if ($reserveItinerary->reserve->application_step == config("consts.reserves.APPLICATION_STEP_RESERVE")) {
            return Response::deny('許可されていないリクエストです(403 Forbidden)');
        }

        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_itineraries', config("consts.agency_roles.DELETE")) && $reserveItinerary->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\ReserveItinerary  $reserveItinerary
     * @return mixed
     */
    public function restore(AppUser $appUser, ReserveItinerary $reserveItinerary)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\ReserveItinerary  $reserveItinerary
     * @return mixed
     */
    public function forceDelete(AppUser $appUser, ReserveItinerary $reserveItinerary)
    {
        // 予約状態の行程は削除不可
        if ($reserveItinerary->reserve->application_step == config("consts.reserves.APPLICATION_STEP_RESERVE")) {
            return Response::deny('許可されていないリクエストです(403 Forbidden)');
        }
        
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_itineraries', config("consts.agency_roles.DELETE")) && $reserveItinerary->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の削除権限がありません(403 Forbidden)');
    }
}
