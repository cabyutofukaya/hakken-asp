<?php

namespace App\Traits;

use App\Models\Reserve;

/**
 * 予約コントローラ用trait
 */
trait ReserveControllerTrait
{
    /**
     * 予約状態をチェックして催行済みの場合は催行済み詳細へ転送
     */
    public function checkReserveState(string $agencyAccount, Reserve $reserve)
    {
        if ($reserve->is_departed) {
            $q = '';
            if (($qp = request()->query())) { // GETクエリがある場合はパラメータもつけて転送
                $q = "?" . http_build_query($qp);
            }
            return redirect(route('staff.estimates.departed.show', [$agencyAccount, $reserve->control_number]) . $q)->throwResponse();
        }
    }
}
