<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\AppUser;
use App\Models\DocumentQuote;
use App\Models\ReserveConfirm;
use App\Models\ReserveItinerary;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReserveConfirmPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any reserve_confirms.
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
            if ($appUser->isApproval('reserve_confirms', config("consts.agency_roles.READ"))) {
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
    public function view(AppUser $appUser, ReserveConfirm $reserveConfirm)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_confirms', config("consts.agency_roles.READ")) && $reserveConfirm->reserve_itinerary->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create reserve_confirms.
     *
     * @param  \App\Models\AppUser  $appUser
     * @return mixed
     */
    public function create(AppUser $appUser, ReserveConfirm $reserveConfirm, ReserveItinerary $reserveItinerary)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_confirms', config("consts.agency_roles.CREATE"))) {
                if ($documentQuoteId = request()->document_quote_id) { // テンプレートIDがPOSTされた場合(store時)。デフォルト系テンプレートの追加は不可
                    $documentQuoteCode = DocumentQuote::where('id', $documentQuoteId)->value("code");
                    if (in_array($documentQuoteCode, config('consts.reserve_confirms.NO_ADD_OR_DELETE_CODE_LIST'), true)) {
                        return Response::deny('許可されていないリクエストです(403 Forbidden)');
                    }
                }
                if ($reserveItinerary->reserve_confirm_num >= config('consts.const.NUMBER_LEDGER_ALLOWED_MAX')) {
                    return Response::deny("帳票の最大作成数(" . config('consts.const.NUMBER_LEDGER_ALLOWED_MAX') . ")を超えています(403 Forbidden)");
                }
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the reserve_confirms.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\MailTemplate  $user
     * @return mixed
     */
    public function update(AppUser $appUser, ReserveConfirm $reserveConfirm)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_confirms', config("consts.agency_roles.UPDATE")) && $reserveConfirm->reserve_itinerary->agency_id == $appUser->agency_id) {
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\MailTemplate  $mailTemplate
     * @return mixed
     */
    public function delete(AppUser $appUser, ReserveConfirm $reserveConfirm)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_confirms', config("consts.agency_roles.DELETE")) && $reserveConfirm->reserve_itinerary->agency_id == $appUser->agency_id && !in_array($reserveConfirm->document_quote->code, config('consts.reserve_confirms.NO_ADD_OR_DELETE_CODE_LIST'), true)) { // 見積・予約確認書デフォルトテンプレートは削除不可
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, ReserveConfirm $reserveConfirm)
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
    public function forceDelete(AppUser $appUser, ReserveConfirm $reserveConfirm)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if ($appUser->isApproval('reserve_confirms', config("consts.agency_roles.DELETE")) && $reserveConfirm->reserve_itinerary->agency_id == $appUser->agency_id && !in_array($reserveConfirm->document_quote->code, config('consts.reserve_confirms.NO_ADD_OR_DELETE_CODE_LIST'), true)) { // 見積・予約確認書デフォルトテンプレートは削除不可
                return Response::allow();
            }
        }
        return Response::deny('予約/見積の削除権限がありません(403 Forbidden)');
    }
}
