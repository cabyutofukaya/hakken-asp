<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\AgencyWithdrawal;
use App\Events\ChangePaymentAmountEvent;
use App\Events\ChangePaymentReserveAmountEvent;
use App\Events\PriceRelatedChangeEvent;
use App\Exceptions\ExclusiveLockException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AccountPayableDetailPaymentBatchRequest;
use App\Http\Requests\Staff\AccountPayableDetailUpdateRequest;
use App\Http\Resources\Staff\AccountPayableDetail\IndexResource;
use App\Models\AccountPayableDetail;
use App\Services\AccountPayableDetailService;
use App\Services\AgencyWithdrawalService;
use App\Services\ReserveItineraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * 仕入れ先買掛金詳細
 */
class AccountPayableDetailController extends Controller
{
    public function __construct(AccountPayableDetailService $accountPayableDetailService, ReserveItineraryService $reserveItineraryService, AgencyWithdrawalService $agencyWithdrawalService)
    {
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->reserveItineraryService = $reserveItineraryService;
        $this->agencyWithdrawalService = $agencyWithdrawalService;
    }

    /**
     * 一覧取得＆表示処理
     *
     * @param array $params 検索パラメータ
     * @param int $limit 取得件数
     * @param string $agencyAccount 会社アカウント
     */
    private function search(array $params, int $limit, string $agencyAccount)
    {
        $search = [];
        // 一応検索に使用するパラメータだけに絞る
        foreach ($params as $key => $val) {
            if (in_array($key, ['payable_number','status','reserve_number','supplier_name','item_name','item_code','last_manager_id','payment_date_from','payment_date_to']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出

                if (in_array($key, ['payment_date_from','payment_date_to'], true) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $search[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $search[$key] = $val;
                }
            }
        }
        
        return IndexResource::collection(
            $this->accountPayableDetailService->paginateByAgencyAccount(
                $agencyAccount,
                $search,
                $limit,
                config('consts.reserves.APPLICATION_STEP_RESERVE'), // スコープ設定は確定済予約情報に
                ['reserve','agency_withdrawals.v_agency_withdrawal_custom_values','saleable.participant:id,name,deleted_at'],
                [],
                true
            )
        );
    }

    /**
     * 仕入一覧
     */
    public function index(Request $request, $agencyAccount)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new AccountPayableDetail);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return $this->search($request->all(), $request->get("per_page", 10), $agencyAccount);
    }

    /**
     * 更新
     *
     * @param int $id account_payable_details ID
     */
    public function update(AccountPayableDetailUpdateRequest $request, $agencyAccount, $id)
    {
        $accountPayableDetail = $this->accountPayableDetailService->find($id);

        if (!$accountPayableDetail) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = \Gate::inspect('update', [$accountPayableDetail]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();
        try {
            if ($accountPayableDetail = $this->accountPayableDetailService->update($accountPayableDetail->id, $input)) {
                return new IndexResource($accountPayableDetail);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (\Exception $e) {
            \Log::error($e);
        }

        abort(500);
    }

    /**
     * 支払い一括処理
     */
    public function paymentBatch(AccountPayableDetailPaymentBatchRequest $request, $agencyAccount)
    {
        $input = $request->only(['data', 'input', 'params']); // 更新対象一覧、form値、検索パラメータ

        $input['input']['agency_id'] = auth('staff')->user()->agency_id; // form値に会社IDをセット

        $changeAtArr = []; // 予約IDごとにバッチ処理時間を記録
        
        try {

            // 出金データを一行ずつ処理
            foreach ($input['data'] as $row) {
                $accountPayableDetail = $this->accountPayableDetailService->find(Arr::get($row, "id"));

                if (!$accountPayableDetail) {
                    throw new NotFoundException("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
                }

                // account_payable_detailsを使い、対象支払いが操作ユーザー会社所有データであることも確認。
                $response = \Gate::inspect('create', [new AgencyWithdrawal, $accountPayableDetail]);
                if (!$response->allowed()) {
                    throw new \Illuminate\Auth\Access\AuthorizationException($response->message());
                }

                // 保存用データ配列に利用者ID、amount、account_payable_detail_id値をセット
                $data = array_merge(
                    $input['input'],
                    [
                        'participant_id' => Arr::get($row, "participant_id"),
                        'supplier_id_log' => Arr::get($row, "supplier_id_log")
                    ],
                    [
                        'amount' => $accountPayableDetail['unpaid_balance'],
                        'account_payable_detail_id' => $accountPayableDetail->id, // 仕入明細ID
                        'reserve_id' => $accountPayableDetail->reserve_id,
                        'reserve_travel_date_id' => $accountPayableDetail->reserve_travel_date_id,
                    ]
                );
                $data['account_payable_detail']['updated_at'] = Arr::get($row, "updated_at"); // 書類更新日時(同時編集チェック用)

                $agencyWithdrawal = \DB::transaction(function () use ($data, &$changeAtArr) {
                    $agencyWithdrawal = $this->agencyWithdrawalService->create($data, true);// 一応、同時編集もチェック
                        
                    // ステータスと支払い残高計算
                    event(new ChangePaymentAmountEvent($agencyWithdrawal->account_payable_detail->id));

                    // 当該予約の支払いステータスと未払金額計算
                    event(new ChangePaymentReserveAmountEvent($agencyWithdrawal->account_payable_detail->reserve_id));

                    $changeAtArr[$agencyWithdrawal->reserve_id] = date('Y-m-d H:i:s'); // PriceRelatedChangeEventに保存する予約IDごとの最新日時を記録

                    return $agencyWithdrawal;
                });
            }

            foreach ($changeAtArr as $reserveId => $updatedAt) {
                event(new PriceRelatedChangeEvent($reserveId, $updatedAt)); // 料金変更に関わるイベントが起きた際に日時を記録
            }
        
            return $this->search(
                $input['params'],
                $request->get("per_page", 10),
                $agencyAccount
            );
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (NotFoundException $e) {
            return response($e->getMessage(), 404);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response($e->getMessage(), 403);
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }
}
