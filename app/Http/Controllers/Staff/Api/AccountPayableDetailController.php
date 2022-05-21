<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\AgencyWithdrawal;
use App\Events\ChangePaymentDetailAmountEvent;
use App\Events\ChangePaymentItemAmountEvent;
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
use App\Services\ReserveBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * 仕入れ先買掛金詳細
 */
class AccountPayableDetailController extends Controller
{
    public function __construct(AccountPayableDetailService $accountPayableDetailService, ReserveItineraryService $reserveItineraryService, AgencyWithdrawalService $agencyWithdrawalService, ReserveBaseService $reserveBaseService)
    {
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->reserveItineraryService = $reserveItineraryService;
        $this->agencyWithdrawalService = $agencyWithdrawalService;
        $this->reserveBaseService = $reserveBaseService;
    }

    /**
     * 一覧取得＆表示処理
     *
     * @param array $params 検索パラメータ
     * @param int $limit 取得件数
     * @param string $agencyAccount 会社アカウント
     */
    private function search(
        int $reserveId,
        int $reserveItineraryId,
        int $supplierId,
        string $subject,
        int $itemId,
        array $params,
        int $limit,
        string $agencyAccount
    )
    {
        $search = [];

        // 必須項目(予約ID、行程ID、仕入先ID、科目、商品ID)は直接検索パラメータにセット
        $search['reserve_id'] = $reserveId;
        $search['reserve_itinerary_id'] = $reserveItineraryId;
        $search['supplier_id'] = $supplierId;
        $search['subject'] = $subject;
        $search['item_id'] = $itemId;

        // 一応検索に使用するパラメータだけに絞る
        foreach ($params as $key => $val) {
            if (in_array($key, ['status', 'item_name', 'participant_name', 'last_manager_id', 'use_date_from', 'use_date_to']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出

                if (in_array($key, ['use_date_from', 'use_date_to'], true) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $search[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $search[$key] = $val;
                }
            }
        }
        
        return IndexResource::collection(
            $this->accountPayableDetailService->paginateByAgencyAccount(
                $agencyAccount,
                $subject,
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
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $reserveHashId 予約ID(ハッシュ)
     * @param string $supplierHashId 支払先ID(ハッシュ)
     * @param string $subject 科目(オプション、航空券、ホテル)
     * @param string $itemHashId 商品ID(ハッシュ)
     */
    public function index(Request $request, string $agencyAccount, string $reserveHashId, string $supplierHashId, string $subject, string $itemHashId)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new AccountPayableDetail);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if (!($reserveId = \Hashids::decode($reserveHashId)[0] ?? null)) {
            abort(404);
        }

        if (!($reserve = $this->reserveBaseService->findForAgencyId($reserveId, auth("staff")->user()->agency_id))) {
            abort(404);
        }

        // 有効行程
        if (!($reserveItineraryId = $reserve->enabled_reserve_itinerary->id)) {
            abort(404);
        }

        // 仕入先ID
        if (!($supplierId = \Hashids::decode($supplierHashId)[0] ?? null)) {
            abort(404);
        }

        // 商品ID
        if (!($itemId = \Hashids::decode($itemHashId)[0] ?? null)) {
            abort(404);
        }

        return $this->search(
            $reserveId,
            $reserveItineraryId,
            $supplierId,
            $subject,
            $itemId,
            $request->all(),
            $request->get("per_page", 10),
            $agencyAccount
        );
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
                    event(new ChangePaymentDetailAmountEvent($agencyWithdrawal->account_payable_detail->id));

                    foreach ($agencyWithdrawal->reserve->reserve_itineraries as $reserveItinerary) {
                        // 当該行程の仕入先＆商品毎のステータスと未払金額計算。
                        event(new ChangePaymentItemAmountEvent($reserveItinerary->id));
                    }

                    // 当該予約の支払いステータスと未払金額計算
                    event(new ChangePaymentReserveAmountEvent($agencyWithdrawal->reserve));

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
