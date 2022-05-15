<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\AgencyWithdrawal;
use App\Events\ChangePaymentDetailAmountEvent;
use App\Events\ChangePaymentReserveAmountEvent;
use App\Events\PriceRelatedChangeEvent;
use App\Exceptions\ExclusiveLockException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AccountPayableItemPaymentBatchRequest;
use App\Http\Requests\Staff\AccountPayableItemPaymentDateUpdateRequest;
use App\Http\Resources\Staff\AccountPayableItem\IndexResource;
use App\Models\AccountPayableItem;
use App\Services\AccountPayableItemService;
use App\Services\AgencyWithdrawalService;
use App\Services\ReserveBaseService;
use App\Services\ReserveItineraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * 仕入れ先買掛金詳細(仕入先＆商品毎)
 */
class AccountPayableItemController extends Controller
{
    public function __construct(AccountPayableItemService $accountPayableItemService, ReserveItineraryService $reserveItineraryService, AgencyWithdrawalService $agencyWithdrawalService, ReserveBaseService $reserveBaseService)
    {
        $this->accountPayableItemService = $accountPayableItemService;
        $this->reserveItineraryService = $reserveItineraryService;
        $this->agencyWithdrawalService = $agencyWithdrawalService;
        $this->reserveBaseService = $reserveBaseService;
    }

    /**
     * 一覧取得＆表示処理
     *
     * @param int $reserveId 予約ID
     * @param array $params 検索パラメータ
     * @param int $limit 取得件数
     * @param string $agencyAccount 会社アカウント
     */
    private function search(int $reserveId, ?int $reserveItineraryId, array $params, int $limit, string $agencyAccount)
    {
        $search = [];
        $search['reserve_id'] = $reserveId; // 必須パラメータ
        $search['reserve_itinerary_id'] = $reserveItineraryId; // 必須パラメータ

        // 一応検索に使用するパラメータだけに絞る
        foreach ($params as $key => $val) {
            if (in_array($key, ['status','reserve_number','supplier_name','item_name','item_code','last_manager_id','payment_date_from','payment_date_to']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出

                if (in_array($key, ['payment_date_from','payment_date_to'], true) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $search[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $search[$key] = $val;
                }
            }
        }
        
        return IndexResource::collection(
            $this->accountPayableItemService->paginateByAgencyAccount(
                $agencyAccount,
                $search,
                $limit,
                config('consts.reserves.APPLICATION_STEP_RESERVE'), // スコープ設定は確定済予約情報に
                ['reserve','agency_withdrawal_item_histories','agency_withdrawal_item_histories.v_agency_withdrawal_item_history_custom_values'],
                [],
                true
            )
        );
    }

    /**
     * 仕入一覧
     *
     * @param string $reserveHashId 予約ID(ハッシュ値)
     */
    public function index(Request $request, $agencyAccount, string $reserveHashId)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new AccountPayableItem);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if (!($reserveId = \Hashids::decode($reserveHashId)[0] ?? null)) {
            abort(404);
        }

        $reserve = $this->reserveBaseService->findForAgencyId($reserveId, auth("staff")->user()->agency_id);

        if(!$reserve){
            abort(404);
        }

        // 有効行程
        $reserveItineraryId = $reserve->enabled_reserve_itinerary->id;

        return $this->search($reserveId, $reserveItineraryId, $request->all(), $request->get("per_page", 10), $agencyAccount);
    }

    /**
     * 更新
     *
     * @param int $id account_payable_details ID
     */
    public function paymentDateUpdate(AccountPayableItemPaymentDateUpdateRequest $request, $agencyAccount, $id)
    {
        $accountPayableItem = $this->accountPayableItemService->find($id);

        if (!$accountPayableItem) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = \Gate::inspect('update', [$accountPayableItem]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $paymentDate = $request->input('payment_date');
        try {
            $newAccountPayableItem = \DB::transaction(function () use ($accountPayableItem, $paymentDate) {
                return $this->accountPayableItemService->paymentDateUpdate($accountPayableItem->id, $paymentDate);
            });

            if ($newAccountPayableItem) {
                return new IndexResource($newAccountPayableItem);
            }

        } catch (\Exception $e) {
            \Log::error($e);
        }

        abort(500);
    }
}
