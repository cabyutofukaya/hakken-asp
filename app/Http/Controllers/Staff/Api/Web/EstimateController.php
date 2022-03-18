<?php

namespace App\Http\Controllers\Staff\Api\Web;

use Hashids;
use App\Exceptions\ExclusiveLockException;
use App\Events\ReserveUpdateStatusEvent;
use App\Events\WebMessageSendEvent;
use App\Events\CreateItineraryEvent;
use App\Events\ReserveChangeRepresentativeEvent;
use App\Events\ReserveChangeHeadcountEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\EstimateConsentRequest;
use App\Http\Requests\Staff\EstimateDetermineRequest;
use App\Http\Requests\Staff\EstimateStatusUpdateRequest;
use App\Http\Resources\Staff\WebEstimate\IndexResource;
use App\Http\Resources\Staff\WebEstimate\ShowResource;
use App\Http\Resources\Staff\WebEstimate\StatusResource;
use App\Http\Resources\Staff\WebReserveExt\ShowResource as WebReserveExtResource;
use App\Models\BusinessUserManager;
use App\Models\Reserve;
use App\Models\User;
use App\Services\WebReserveEstimateService;
use App\Services\WebReserveExtService;
use App\Services\WebEstimateService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveCustomValueService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Services\VAreaService;
use App\Services\WebMessageService;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Arr;

class EstimateController extends Controller
{
    public function __construct(UserService $userService, VAreaService $vAreaService, WebReserveEstimateService $webReserveEstimateService, WebEstimateService $webEstimateService, ReserveCustomValueService $reserveCustomValueService, UserCustomItemService $userCustomItemService, ReserveInvoiceService $reserveInvoiceService, WebReserveExtService $webReserveExtService, WebMessageService $webMessageService)
    {
        $this->webReserveEstimateService = $webReserveEstimateService;
        $this->webEstimateService = $webEstimateService;
        $this->userService = $userService;
        $this->vAreaService = $vAreaService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->userCustomItemService = $userCustomItemService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->webReserveExtService = $webReserveExtService;
        $this->webMessageService = $webMessageService;
    }

    // 一件取得
    public function show(string $agencyAccount, string $estimateNumber)
    {
        $estimate = $this->webEstimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

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
            if (in_array($key, ['record_number', 'departure_date', 'return_date', 'departure', 'destination', 'applicant', 'representative']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出

                if (in_array($key, ['departure_date','return_date'], true) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }
            }
        }

        return IndexResource::collection(
            $this->webEstimateService->paginateByAgencyAccount(
                $agencyAccount,
                $params,
                request()->get("per_page", 10),
                [
                    'web_reserve_ext.web_online_schedule',
                    'manager',
                    'departure',
                    'destination',
                    'travel_type',
                    'application_type',
                    'statuses',
                    'application_date',
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
        $estimate = $this->webEstimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        if (!$estimate) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('update', [$estimate]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // TODO この辺からしっかりみて実装

        $input = $request->only('updated_at');
        try {
            $reserve = DB::transaction(function () use ($estimate, $input) {
                if ($this->webEstimateService->determine(
                    $estimate,
                    $input,
                    $this->userCustomItemService,
                    $this->reserveCustomValueService,
                    $this->webReserveEstimateService
                )) {
                    // 有効な旅程があれば旅程作成イベントを実行（予約確認書・請求書作成処理）
                    $reserve = $this->webReserveEstimateService->find($estimate->id);

                    if ($reserve->enabled_reserve_itinerary->id) {
                        event(new CreateItineraryEvent($reserve->enabled_reserve_itinerary));
                    }

                    // ステータス更新イベント
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

        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");

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
        $estimate = $this->webEstimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        if (!$estimate) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $response = Gate::authorize('updateStatus', $estimate);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            // ↓この判定はそれほど重要ではなさそうなので一旦外し
            // // ステータスが「キャンセル」になった後にステータス変更されると困るので同時編集チェック
            // if ($estimate->updated_at != Arr::get($input, 'updated_at')) {
            //     throw new ExclusiveLockException;
            // }

            // ステータスのカスタム項目を取得
            $customStatus = $this->userCustomItemService->findByCodeForAgency($estimate->agency_id, config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS'), ['key'], null);
    
            if ($customStatus) {
                $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => $input['status']], $estimate->id); // カスタムフィールド保存
    
                // ステータス更新イベント
                $newEstimate = $this->webEstimateService->find($estimate->id);
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
     * @param string $hashId ハッシュID
     */
    public function destroy(Request $request, $agencyAccount, string $hashId)
    {
        $id = Hashids::decode($hashId)[0] ?? 0;
        $reserve = $this->webEstimateService->find((int)$id);

        if (!$reserve) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if ($this->webEstimateService->delete($reserve->id, true)) { // 論理削除

            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "{$reserve->estimate_number}」の削除が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }

            return response('', 200);
        }
        abort(500);
    }

    /**
     * 依頼受付
     * いわゆる見積もりのstoreと同じと思われる
     *
     * @param string $requestNumber 依頼番号
     */
    public function consent(EstimateConsentRequest $request, string $agencyAccount, string $requestNumber)
    {
        $reserve = $this->webEstimateService->findByRequestNumber($requestNumber, $agencyAccount, ['web_reserve_ext']);

        if (!data_get($reserve, 'web_reserve_ext')) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック(reservesとweb_reserve_exts)
        $response = Gate::inspect('update', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        $response = Gate::inspect('consent', [$reserve->web_reserve_ext]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $webUser = $reserve->web_reserve_ext->web_consult->web_user; // 申込者

        $message = $request->input("message");
        try {
            $reserve = \DB::transaction(function () use ($agencyAccount, $reserve, $message, $webUser) {
                // 予約拡張レコードを承諾状態に設定
                $this->webReserveExtService->consent($reserve->web_reserve_ext->id);

                // メッセージがあれば送信
                if ($message) {
                    $staff = auth("staff")->user();

                    $webMessage = $this->webMessageService->create([
                        'agency_id' => $reserve->agency_id,
                        'reserve_id' => $reserve->id,
                        'senderable_type' => get_class($staff),
                        'senderable_id' => $staff->id,
                        'message' => $message,
                        'send_at' => date('Y-m-d H:i:s'),
                    ]);

                    //　メッセージ作成イベント(→ユーザー側の未読数を更新等)
                    event(new WebMessageSendEvent($webMessage));
                }

                // 顧客情報を取得。未登録の場合は作成
                if (!($user = $this->userService->findByWebUserId($webUser->id, $reserve->agency_id, false))) {
                    // Webユーザーから個人顧客レコードを作成
                    $user = $this->userService->createFromWebUser($webUser, ['agency_id' => $reserve->agency_id], true);
                }

                // 当該reservesレコードを承諾状態に設定＆個人申し込みの場合は申込者を参加者に設定
                $this->webEstimateService->consent($agencyAccount, $reserve->id, $reserve->agency_id, $user);

                //　ステータスのカスタム項目値を「見積」にセット
                $this->reserveCustomValueService->setValuesForCodes(
                    [
                        config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS') => config('consts.reserves.ESTIMATE_DEFAULT_STATUS')
                    ],
                    $reserve->agency_id,
                    $reserve->id
                );

                $reserve = $this->webEstimateService->find($reserve->id);

                // ステータス更新イベント
                event(new ReserveUpdateStatusEvent($reserve));
                
                event(new ReserveChangeRepresentativeEvent($reserve)); // 代表者変更イベント

                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント

                return $reserve;
            });
            
            if ($reserve) {
                if ($request->input("set_message")) {
                    $request->session()->flash('success_message', "依頼番号「{$requestNumber}」の相談を受け付けました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return new ShowResource($reserve);
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 相談依頼辞退
     *
     * @param string $requestNumber 依頼番号
     */
    public function reject(string $agencyAccount, string $requestNumber)
    {
        $reserve = $this->webEstimateService->findByRequestNumber($requestNumber, $agencyAccount, ['web_reserve_ext']);

        if (!data_get($reserve, 'web_reserve_ext')) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('reject', [$reserve->web_reserve_ext]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $result = \DB::transaction(function () use ($reserve) {
                return $this->webReserveExtService->reject($reserve->web_reserve_ext->id);
            });

            if ($result) {
                return new WebReserveExtResource($this->webReserveExtService->find($reserve->web_reserve_ext->id));
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }
}
