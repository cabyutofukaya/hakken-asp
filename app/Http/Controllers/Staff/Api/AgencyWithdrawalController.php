<?php

namespace App\Http\Controllers\Staff\Api;

use App\Events\ChangePaymentAmountEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AgencyWithdrawalStoreRequest;
use App\Http\Resources\Staff\AccountPayableDetail\IndexResource;
use App\Services\AccountPayableDetailService;
use App\Services\AgencyWithdrawalService;

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
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // agency_withdrawalsテーブルに対するcreate権限をチェックしてもセキュリティ上あまり意味がないので、account_payable_detailsテーブルに対する編集権限をチェック
        $response = \Gate::authorize('update', $accountPayableDetail);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();//カスタムフィールドがあるのでallで取得

        $input['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット
        $input['reserve_id'] = $accountPayableDetail->reserve_id; // 予約ID
        $input['account_payable_detail_id'] = $accountPayableDetail->id; // 仕入明細ID
        $input['reserve_travel_date_id'] = $accountPayableDetail->reserve_travel_date_id; // 旅行日ID

        try {
            $agencyWithdrawal = \DB::transaction(function () use ($input) {
                $agencyWithdrawal = $this->agencyWithdrawalService->create($input);
                
                // ステータスと支払い残高計算
                event(new ChangePaymentAmountEvent($agencyWithdrawal->account_payable_detail->id));

                return $agencyWithdrawal;
            });

            if ($agencyWithdrawal) {
                $accountPayableDetail = $this->accountPayableDetailService->find($agencyWithdrawal->account_payable_detail_id, ['reserve','supplier', 'agency_withdrawals.v_agency_withdrawal_custom_values'], );

                // 当該出金データの親レコードとなる仕入詳細データを返す
                return new IndexResource($accountPayableDetail, 201);
                // return new StoreResource($agencyWithdrawal, 201);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return response("他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 409);
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 出金データ削除
     */
    public function destroy($agencyAccount, $agencyWithdrawalId)
    {
        $agencyWithdrawal = $this->agencyWithdrawalService->find((int)$agencyWithdrawalId);

        if (!$agencyWithdrawal) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        $response = \Gate::authorize('delete', $agencyWithdrawal);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $result = \DB::transaction(function () use ($agencyWithdrawal) {
            $this->agencyWithdrawalService->delete($agencyWithdrawal->id, true); // 論理削除

            event(new ChangePaymentAmountEvent($agencyWithdrawal->account_payable_detail_id));

            return true;
        });

        if ($result) {
            $accountPayableDetail = $this->accountPayableDetailService->find($agencyWithdrawal->account_payable_detail_id, ['reserve','supplier', 'agency_withdrawals.v_agency_withdrawal_custom_values'], );

            // 当該出金データの親レコードとなる仕入詳細データを返す
            return new IndexResource($accountPayableDetail, 200);
        }
        abort(500);
    }
}
