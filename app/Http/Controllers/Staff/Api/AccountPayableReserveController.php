<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\AgencyWithdrawal;
use App\Events\ChangePaymentDetailAmountEvent;
use App\Events\ChangePaymentReserveAmountEvent;
use App\Events\PriceRelatedChangeEvent;
use App\Exceptions\ExclusiveLockException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AccountPayableReservePaymentBatchRequest;
use App\Http\Requests\Staff\AccountPayableReserveUpdateRequest;
use App\Http\Resources\Staff\AccountPayableReserve\IndexResource;
use App\Models\AccountPayableReserve;
use App\Services\AccountPayableReserveService;
use App\Services\AgencyWithdrawalService;
use App\Services\ReserveItineraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * 仕入先買掛金管理(予約毎)
 */
class AccountPayableReserveController extends Controller
{
    public function __construct(AccountPayableReserveService $accountPayableReserveService, ReserveItineraryService $reserveItineraryService, AgencyWithdrawalService $agencyWithdrawalService)
    {
        $this->accountPayableReserveService = $accountPayableReserveService;
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
            if (in_array($key, ['status','reserve_number','manager_id','departure_date_from','departure_date_to'])) {

                if (in_array($key, ['departure_date_from','departure_date_to'], true)) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $search[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $search[$key] = $val;
                }
            }
        }
        
        return IndexResource::collection(
            $this->accountPayableReserveService->paginateByAgencyAccount(
                $agencyAccount,
                $search,
                $limit,
                config('consts.reserves.APPLICATION_STEP_RESERVE'), // スコープ設定は確定済予約情報に
                ['reserve.manager'],
                [],
                false
            )
        ); // 仕入額・未払額が0円のレコードも取得(第7引数)
    }

    /**
     * (予約毎)仕入一覧
     */
    public function index(Request $request, $agencyAccount)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new AccountPayableReserve);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return $this->search($request->all(), $request->get("per_page", 10), $agencyAccount);
    }

    // /**
    //  * 更新
    //  *
    //  * @param int $id account_payable_details ID
    //  */
    // public function update(AccountPayableReserveUpdateRequest $request, $agencyAccount, $id)
    // {
    //     $accountPayableReserve = $this->accountPayableReserveService->find($id);

    //     if (!$accountPayableReserve) {
    //         abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
    //     }

    //     // 認可チェック
    //     $response = \Gate::inspect('update', [$accountPayableReserve]);
    //     if (!$response->allowed()) {
    //         abort(403, $response->message());
    //     }

    //     $input = $request->all();
    //     try {
    //         if ($accountPayableReserve = $this->accountPayableReserveService->update($accountPayableReserve->id, $input)) {
    //             return new IndexResource($accountPayableReserve);
    //         }
    //     } catch (ExclusiveLockException $e) { // 同時編集エラー
    //         abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
    //     } catch (\Exception $e) {
    //         \Log::error($e);
    //     }

    //     abort(500);
    // }

}
