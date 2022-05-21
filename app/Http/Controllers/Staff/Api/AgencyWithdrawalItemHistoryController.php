<?php

namespace App\Http\Controllers\Staff\Api;

use App\Events\ChangePaymentDetailAmountEvent;
use App\Events\ChangePaymentDetailAmountForItemEvent;
use App\Events\ChangePaymentItemAmountEvent;
use App\Events\ChangePaymentReserveAmountEvent;
use App\Events\PriceRelatedChangeEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AgencyWithdrawalItemHistoryDeleteRequest;
use App\Http\Requests\Staff\AgencyWithdrawalItemHistoryStoreRequest;
use App\Http\Resources\Staff\AccountPayableItem\IndexResource;
use App\Models\AgencyWithdrawalItemHistory;
use App\Services\AccountPayableItemService;
use App\Services\AgencyWithdrawalItemHistoryService;
use App\Services\AgencyWithdrawalService;
use Illuminate\Support\Arr;

/**
 * 出金管理(商品毎)
 */
class AgencyWithdrawalItemHistoryController extends Controller
{
    public function __construct(AgencyWithdrawalItemHistoryService $agencyWithdrawalItemHistoryService, AccountPayableItemService $accountPayableItemService, AgencyWithdrawalService $agencyWithdrawalService)
    {
        $this->agencyWithdrawalItemHistoryService = $agencyWithdrawalItemHistoryService;
        $this->accountPayableItemService = $accountPayableItemService;
        $this->agencyWithdrawalService = $agencyWithdrawalService;
    }

    /**
     * 出金登録
     */
    public function store(AgencyWithdrawalItemHistoryStoreRequest $request, $agencyAccount, $accountPayableItemId)
    {
        $accountPayableItem = $this->accountPayableItemService->find($accountPayableItemId);

        // 認可チェック
        if (!$accountPayableItem) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // account_payable_itemsを使い、対象支払いが操作ユーザー会社所有データであることも確認
        $response = \Gate::authorize('create', [new AgencyWithdrawalItemHistory, $accountPayableItem]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();//カスタムフィールドがあるのでallで取得

        $input['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット
        $input['reserve_id'] = $accountPayableItem->reserve_id; // 予約ID
        $input['account_payable_item_id'] = $accountPayableItem->id; // 仕入(商品毎)明細ID
        $input['payment_type'] = config('consts.agency_withdrawal_item_histories.PAYMENT_TYPE_BULK'); // 出金種別を「一括出金に設定」

        try {
            $agencyWithdrawalItemHistory = \DB::transaction(function () use ($input, $accountPayableItem) {

                // 一括出金識別キー
                $bulkWithdrawalKey = $this->agencyWithdrawalItemHistoryService->generateWithdrawalKey();
                $input['bulk_withdrawal_key'] = $bulkWithdrawalKey;

                $agencyWithdrawalItemHistory = $this->agencyWithdrawalItemHistoryService->create($input); // 出金登録。同時編集チェック。出金登録リクエストと入れ違いで行程が変更された(仕入先が変更された等)場合などは例外が投げられる

                // 当該仕入(account_payable_item)に属する商品に対しての出金登録とカスタム項目の一括登録
                $this->agencyWithdrawalService->bulkCreateForItem($input, $bulkWithdrawalKey, $accountPayableItem, config('consts.reserves.APPLICATION_STEP_RESERVE'));

                
                // 仕入詳細のステータスと支払残高計算
                event(new ChangePaymentDetailAmountForItemEvent($accountPayableItem));

                // 当該行程の仕入先＆商品毎のステータスと未払金額計算。
                event(new ChangePaymentItemAmountEvent($accountPayableItem->reserve_itinerary_id));

                // 当該予約の支払いステータスと未払金額計算
                event(new ChangePaymentReserveAmountEvent($agencyWithdrawalItemHistory->reserve));

                event(new PriceRelatedChangeEvent($agencyWithdrawalItemHistory->reserve_id, date('Y-m-d H:i:s'))); // 料金変更に関わるイベントが起きた際に日時を記録

                return $agencyWithdrawalItemHistory;
            });

            if ($agencyWithdrawalItemHistory) {
                $accountPayableItem = $this->accountPayableItemService->find(
                    $agencyWithdrawalItemHistory->account_payable_item_id,
                    ['reserve','agency_withdrawal_item_histories.v_agency_withdrawal_item_history_custom_values','agency_withdrawal_item_histories.agency_withdrawal.v_agency_withdrawal_custom_values']
                );

                // 当該出金データの親レコードとなる仕入詳細データを返す
                return new IndexResource($accountPayableItem, 201);
                // return new StoreResource($agencyWithdrawalItemHistory, 201);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "料金情報が変更されたか他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (\Exception $e) {
            \Log::error($e);
            abort(500, $e->getMessage());
        }
        abort(500);
    }

    /**
     * 出金データ削除
     */
    public function destroy(AgencyWithdrawalItemHistoryDeleteRequest $request, $agencyAccount, int $agencyWithdrawalItemHistoryId)
    {
        $agencyWithdrawalItemHistory = $this->agencyWithdrawalItemHistoryService->find($agencyWithdrawalItemHistoryId);

        if (!$agencyWithdrawalItemHistory) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        $response = \Gate::authorize('delete', $agencyWithdrawalItemHistory);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            if ($agencyWithdrawalItemHistory->account_payable_item->updated_at != Arr::get($input, 'account_payable_item.updated_at')) { // 念の為、同時編集チェック
                throw new ExclusiveLockException;
            }

            $result = \DB::transaction(function () use ($agencyWithdrawalItemHistory) {
                $this->agencyWithdrawalItemHistoryService->delete($agencyWithdrawalItemHistory->id, true); // 論理削除

                // 当該出金に紐づく個別出金登録を削除
                $this->agencyWithdrawalService->deleteByBulkWithdrawalKey($agencyWithdrawalItemHistory->bulk_withdrawal_key, true); // 論理削除


                // // 出金種別によって処理を変える
                // if ($agencyWithdrawalItemHistory->payment_type == config('consts.agency_withdrawal_item_histories.PAYMENT_TYPE_BULK')) { // 一括出金
                //     // 当該出金に紐づく個別出金登録を削除
                //     $this->agencyWithdrawalService->deleteByBulkWithdrawalKey($agencyWithdrawalItemHistory->bulk_withdrawal_key, true); // 論理削除
                // } elseif ($agencyWithdrawalItemHistory->payment_type == config('consts.agency_withdrawal_item_histories.PAYMENT_TYPE_INDIVIDUAL')) { // 個別出金
                //     // 出金詳細レコード削除
                //     $this->agencyWithdrawalService->delete($agencyWithdrawalItemHistory->agency_withdrawal_id, true); // 論理削除
                // } elseif ($agencyWithdrawalItemHistory->payment_type == config('consts.agency_withdrawal_item_histories.PAYMENT_TYPE_BULK_DELETE_FOR_INDIVIDUAL')) { // 個別一括出金削除
                //     // 出金詳細レコードの復元
                //     $this->agencyWithdrawalService->restore($agencyWithdrawalItemHistory->agency_withdrawal_id);
                // }

                
                // 仕入詳細のステータスと支払残高計算
                event(new ChangePaymentDetailAmountForItemEvent($agencyWithdrawalItemHistory->account_payable_item));

                // 当該行程の仕入先＆商品毎のステータスと未払金額計算。
                event(new ChangePaymentItemAmountEvent($agencyWithdrawalItemHistory->account_payable_item->reserve_itinerary_id));
                
                // 当該予約の支払いステータスと未払金額計算
                event(new ChangePaymentReserveAmountEvent($agencyWithdrawalItemHistory->reserve));

                event(new PriceRelatedChangeEvent($agencyWithdrawalItemHistory->reserve_id, date('Y-m-d H:i:s'))); // 料金変更に関わるイベントが起きた際に日時を記録
    
                return true;
            });
    
            if ($result) {
                $accountPayableItem = $this->accountPayableItemService->find($agencyWithdrawalItemHistory->account_payable_item_id, ['reserve','supplier','agency_withdrawal_item_histories','agency_withdrawal_item_histories.v_agency_withdrawal_item_history_custom_values'], );
    
                // 当該出金データの親レコードとなる仕入詳細データを返す
                return new IndexResource($accountPayableItem, 200);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "料金情報が変更されたか他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }
}
