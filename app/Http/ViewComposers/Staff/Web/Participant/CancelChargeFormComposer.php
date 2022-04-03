<?php
namespace App\Http\ViewComposers\Staff\Web\Participant;

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
    
    public function __construct(
    ) {
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $participant = Arr::get($data, 'participant');
        $reserve = Arr::get($data, 'reserve');
        $purchasingList = Arr::get($data, 'purchasingList');

        ////////////
        
        $agencyAccount = request()->agencyAccount;

        $defaultValue = session()->getOldInput();
        if (!isset($defaultValue['rows'])) {
            $defaultValue['rows'] = $purchasingList;
            // 初期設定時はis_cancelはtrueで初期化
            foreach ($defaultValue['rows'] as $key => $row) {
                if ($row['purchase_type'] == config('consts.const.PURCHASE_NORMAL')) { // 通常仕入行の場合はis_cancelはtrueで初期化
                    $defaultValue['rows'][$key]['is_cancel'] = 1;
                } elseif ($row['purchase_type'] == config('consts.const.PURCHASE_CANCEL')) { // キャンセル仕入の場合はis_cancelの値で初期化
                    $defaultValue['rows'][$key]['is_cancel'] = Arr::get($row, 'is_cancel', 0);
                }
            }
        }
        if (!isset($defaultValue['reserve']['updated_at'])) {
            $defaultValue['reserve']['updated_at'] = $reserve->updated_at->format('Y-m-d H:i:s');
        }

        $consts = [
            'reserveUrl' => route('staff.web.estimates.reserve.show', [$agencyAccount, $reserve->control_number]) . "?tab=" . config('consts.reserves.TAB_RESERVE_DETAIL'),
            'cancelChargeUpdateUrl' => route('staff.api.participant_cancel_charge.update',[$agencyAccount, config('consts.const.RECEPTION_TYPE_WEB'), $participant->id]),
            'cancelChargeUpdateAfterUrl' => $reserve->is_departed ? route('staff.estimates.departed.show', ['agencyAccount' => $agencyAccount, 'reserveNumber' => $reserve->control_number, 'tab' => config('consts.reserves.TAB_RESERVE_DETAIL')]) : route('staff.web.estimates.reserve.show', ['agencyAccount' => $agencyAccount, 'reserveNumber' => $reserve->control_number, 'tab' => config('consts.reserves.TAB_RESERVE_DETAIL')]), // キャンセルチャージ処理後のリダイレクト先。催行済か否かで出し分け
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('defaultValue', 'consts', 'jsVars'));
    }
}
