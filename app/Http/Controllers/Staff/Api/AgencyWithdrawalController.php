<?php

namespace App\Http\Controllers\Staff\Api;

use App\Events\ChangePaymentDetailAmountEvent;
use App\Events\ChangePaymentItemAmountEvent;
use App\Events\ChangePaymentReserveAmountEvent;
use App\Events\PriceRelatedChangeEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AgencyWithdrawalDeleteRequest;
use App\Http\Requests\Staff\AgencyWithdrawalStoreRequest;
use App\Http\Resources\Staff\AccountPayableDetail\IndexResource;
use App\Models\AgencyWithdrawal;
use App\Services\AccountPayableDetailService;
use App\Services\AgencyWithdrawalService;
use Illuminate\Support\Arr;

/**
 * 出金管理
 */
class AgencyWithdrawalController extends Controller
{
    public function __construct(AgencyWithdrawalService $agencyWithdrawalService, AccountPayableDetailService $accountPayableDetailService)
    {
        $this->agencyWithdrawalService = $agencyWithdrawalService;
        $this->accountPayableDetailService = $accountPayableDetailService;
    }

    /**
     * 出金登録
     */
    public function store(AgencyWithdrawalStoreRequest $request, $agencyAccount, $accountPayableDetailId)
    {
        $accountPayableDetail = $this->accountPayableDetailService->find($accountPayableDetailId);

        // 認可チェック
        if (!$accountPayableDetail) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // account_payable_detailsを使い、対象支払いが操作ユーザー会社所有データであることも確認
        $response = \Gate::authorize('create', [new AgencyWithdrawal, $accountPayableDetail]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();//カスタムフィールドがあるのでallで取得

        $input['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット
        $input['reserve_id'] = $accountPayableDetail->reserve_id; // 予約ID
        $input['account_payable_detail_id'] = $accountPayableDetail->id; // 仕入明細ID
        $input['reserve_travel_date_id'] = $accountPayableDetail->reserve_travel_date_id; // 旅行日ID

        try {
            $agencyWithdrawal = \DB::transaction(function () use ($input, $accountPayableDetail) {
                $agencyWithdrawal = $this->agencyWithdrawalService->create($input); // 同時編集チェック。出金登録リクエストと入れ違いで行程が変更された(仕入先が変更された等)場合などは例外が投げられる
                
                // ステータスと支払い残高計算
                event(new ChangePaymentDetailAmountEvent($agencyWithdrawal->account_payable_detail->id));

                // 当該行程の仕入先＆商品毎のステータスと未払金額計算。
                event(new ChangePaymentItemAmountEvent($accountPayableDetail->reserve_itinerary_id));

                // 当該予約の支払いステータスと未払金額計算
                event(new ChangePaymentReserveAmountEvent($agencyWithdrawal->reserve));

                event(new PriceRelatedChangeEvent($agencyWithdrawal->reserve_id, date('Y-m-d H:i:s'))); // 料金変更に関わるイベントが起きた際に日時を記録

                return $agencyWithdrawal;
            });

            if ($agencyWithdrawal) {
                $accountPayableDetail = $this->accountPayableDetailService->find($agencyWithdrawal->account_payable_detail_id, ['reserve','supplier', 'agency_withdrawals.v_agency_withdrawal_custom_values'], );

                // 当該出金データの親レコードとなる仕入詳細データを返す
                return new IndexResource($accountPayableDetail, 201);
                // return new StoreResource($agencyWithdrawal, 201);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "料金情報が変更されたか他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 出金データ削除
     */
    public function destroy(AgencyWithdrawalDeleteRequest $request, $agencyAccount, $agencyWithdrawalId)
    {
        $agencyWithdrawal = $this->agencyWithdrawalService->find((int)$agencyWithdrawalId);

        if (!$agencyWithdrawal) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        $response = \Gate::authorize('delete', $agencyWithdrawal);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            if ($agencyWithdrawal->account_payable_detail->updated_at != Arr::get($input, 'account_payable_detail.updated_at')) { // 念の為、同時編集チェック
                throw new ExclusiveLockException;
            }
            
            $result = \DB::transaction(function () use ($agencyWithdrawal) {
                $this->agencyWithdrawalService->delete($agencyWithdrawal->id, true); // 論理削除
    
                event(new ChangePaymentDetailAmountEvent($agencyWithdrawal->account_payable_detail_id));

                // 当該行程の仕入先＆商品毎のステータスと未払金額計算。
                event(new ChangePaymentItemAmountEvent($agencyWithdrawal->account_payable_detail->reserve_itinerary_id));
                
                // 当該予約の支払いステータスと未払金額計算
                event(new ChangePaymentReserveAmountEvent($agencyWithdrawal->reserve));

                event(new PriceRelatedChangeEvent($agencyWithdrawal->reserve_id, date('Y-m-d H:i:s'))); // 料金変更に関わるイベントが起きた際に日時を記録
    
                return true;
            });
    
            if ($result) {
                $accountPayableDetail = $this->accountPayableDetailService->find($agencyWithdrawal->account_payable_detail_id, ['reserve','supplier', 'agency_withdrawals.v_agency_withdrawal_custom_values']);
    
                // 当該出金データの親レコードとなる仕入詳細データを返す
                return new IndexResource($accountPayableDetail, 200);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "料金情報が変更されたか他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }
}
