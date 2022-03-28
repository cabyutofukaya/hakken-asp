<?php
namespace App\Http\ViewComposers\Staff\ReserveEstimate;

use Illuminate\View\View;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Request;

/**
 * キャンセルチャージ設定ページに使う選択項目などを提供するViewComposer
 */
class CancelChargeFormComposer
{
    use JsConstsTrait;
    
    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserve = Arr::get($data, 'reserve');
        $purchasingList = Arr::get($data, 'purchasingList');

        ////////////

        $agencyAccount = request()->agencyAccount;

        $defaultValue = session()->getOldInput();
        if (!isset($defaultValue['rows'])) {
            $defaultValue['rows'] = $purchasingList;
            // 初期設定時はis_cancelはtrueで初期化
            foreach ($defaultValue['rows'] as $key => $row) {
                $defaultValue['rows'][$key]['is_cancel'] = 1;
            }
        }
        if (!isset($defaultValue['reserve']['updated_at'])) {
            $defaultValue['reserve']['updated_at'] = $reserve->updated_at->format('Y-m-d H:i:s');
        }

        $consts = [
            'reserveUrl' => route('staff.asp.estimates.reserve.show', [$agencyAccount, $reserve->control_number]),
            'cancelChargeUpdateUrl' => route('staff.asp.estimates.reserve.cancel_charge.update', [$agencyAccount, $reserve->control_number]),
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('defaultValue', 'consts', 'jsVars'));
    }
}
