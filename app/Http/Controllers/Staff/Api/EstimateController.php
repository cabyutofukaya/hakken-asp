<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Events\ReserveUpdateStatusEvent;
use App\Events\CreateItineraryEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CustomerSearchRequest;
use App\Http\Requests\Staff\EstimateDetermineRequest;
use App\Http\Requests\Staff\EstimateStatusUpdateRequest;
use App\Http\Resources\Staff\Estimate\IndexResource;
use App\Http\Resources\Staff\Estimate\ShowResource;
use App\Http\Resources\Staff\Estimate\StatusResource;
use App\Models\BusinessUserManager;
use App\Models\Reserve;
use App\Models\User;
use App\Services\BusinessUserManagerService;
use App\Services\EstimateService;
use App\Services\ReserveEstimateService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveCustomValueService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Services\VAreaService;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Arr;

class EstimateController extends Controller
{
    public function __construct(UserService $userService, BusinessUserManagerService $businessUserManagerService, VAreaService $vAreaService, EstimateService $estimateService, ReserveCustomValueService $reserveCustomValueService, UserCustomItemService $userCustomItemService, ReserveInvoiceService $reserveInvoiceService, ReserveEstimateService $reserveEstimateService)
    {
        $this->estimateService = $estimateService;
        $this->userService = $userService;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->vAreaService = $vAreaService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->userCustomItemService = $userCustomItemService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->reserveEstimateService = $reserveEstimateService;
    }

    // 一件取得
    public function show($agencyAccount, $estimateNumber)
    {
        $estimate = $this->estimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        if (!$estimate) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('view', [$estimate]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return new ShowResource($estimate);
    }

    // 一覧
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new Reserve);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        // applicant -> 申込者
        // representative -> 代表参加者
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['estimate_number', 'departure_date', 'return_date', 'departure', 'destination', 'applicant', 'representative']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出

                if (in_array($key, ['departure_date','return_date'], true) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }
            }
        }

        return IndexResource::collection(
            $this->estimateService->paginateByAgencyAccount(
                $agencyAccount,
                $params,
                request()->get("per_page", 10),
                [
                    'manager',
                    'departure',
                    'destination',
                    'travel_types',
                    'application_type',
                    'statuses',
                    'application_dates',
                    'applicantable',
                    'representatives.user'
                ]
            )
        );
    }

    /**
     * 見積もり確定
     * （見積状態を予約に変更）
     *
     * @param string $agencyAccount 会社アカウント
     */
    public function determine(EstimateDetermineRequest $request, $agencyAccount, $estimateNumber)
    {
        $estimate = $this->estimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        if (!$estimate) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('update', [$estimate]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->only('updated_at');
        try {
            $reserve = DB::transaction(function () use ($estimate, $input) {
                if ($this->estimateService->determine(
                    $estimate,
                    $input,
                    $this->userCustomItemService,
                    $this->reserveCustomValueService,
                    $this->reserveEstimateService
                )) {
                    $reserve = $this->reserveEstimateService->find($estimate->id);

                    // 有効な旅程があれば旅程作成イベントを実行（予約確認書・請求書作成処理）
                    if ($reserve->enabled_reserve_itinerary->id) {
                        event(new CreateItineraryEvent($reserve->enabled_reserve_itinerary));
                    }

                    // ステータス更新イベント Web受付用なので不要
                    event(new ReserveUpdateStatusEvent($reserve));

                    return $reserve;
                }
            });

            if ($reserve) {
                if ($request->input("set_message")) {
                    $request->session()->flash('success_message', "予約確定処理が完了しました「{$reserve->control_number}」。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return response('', 200);
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * ステータス更新
     */
    public function statusUpdate(EstimateStatusUpdateRequest $request, $agencyAccount, $estimateNumber)
    {
        $estimate = $this->estimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        if (!$estimate) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $response = Gate::authorize('update', $estimate);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            // ステータスが「キャンセル」になった後にステータス変更されると困るので同時編集チェック
            if ($estimate->updated_at != Arr::get($input, 'updated_at')) {
                throw new ExclusiveLockException;
            }

            // ステータスのカスタム項目を取得
            $customStatus = $this->userCustomItemService->findByCodeForAgency($estimate->agency_id, config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS'), ['key'], null);
    
            if ($customStatus) {
                $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => $input['status']], $estimate->id); // カスタムフィールド保存

                // ステータス更新イベント
                $newEstimate = $this->estimateService->find($estimate->id);
                event(new ReserveUpdateStatusEvent($newEstimate));
                
                return new StatusResource($newEstimate);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }

    /**
     * 一件削除
     *
     * @param string $estimateNumber 見積番号
     */
    public function destroy(Request $request, $agencyAccount, $estimateNumber)
    {
        $reserve = $this->estimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        if (!$reserve) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if ($this->estimateService->delete($reserve->id, true)) { // 論理削除

            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "{$estimateNumber}」の削除が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }

            return response('', 200);
        }
        abort(500);
    }
}
