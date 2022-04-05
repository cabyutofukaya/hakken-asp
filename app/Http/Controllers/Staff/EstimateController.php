<?php

namespace App\Http\Controllers\Staff;

use App\Events\ReserveChangeHeadcountEvent;
use App\Events\ReserveChangeRepresentativeEvent;
use App\Events\UpdatedReserveEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\EstimateStoretRequest;
use App\Http\Requests\Staff\EstimateUpdateRequest;
use App\Models\Reserve;
use App\Services\EstimateService;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;

class EstimateController extends AppController
{
    public function __construct(EstimateService $estimateService)
    {
        $this->estimateService = $estimateService;
    }

    /**
     * 見積一覧
     */
    public function index()
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new Reserve]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        return view('staff.estimate.index');
    }

    /**
     * 詳細表示ページ
     *
     * @param string $estimateNumber 見積番号
     */
    public function show($agencyAccount, $estimateNumber)
    {
        $reserve = $this->estimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 予約に切り替わった場合は転送
        $this->checkEstimateState($agencyAccount, $reserve);

        return view('staff.estimate.show', compact('reserve'));
    }

    /**
     * 新規作成
     */
    public function create()
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        $applicationStep = config('consts.reserves.APPLICATION_STEP_DRAFT'); // 申込段階。CreateFormComposer内で見積と予約で処理を分ける際に使用する変数

        return view('staff.estimate.create', compact('applicationStep'));
    }

    /**
     * 登録処理
     * バリデーションは予約作成時と共通(ReserveStoretRequest)
     *
     * @param string $agencyAccount 会社アカウント
     */
    public function store(EstimateStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Reserve]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $agencyId = auth('staff')->user()->agency_id;

        $input = $request->all(); // カスタムフィールドがあるので、validatedではなくallで取得

        $input['agency_id'] = $agencyId; // 会社IDをセット

        try {
            $reserve = DB::transaction(function () use ($agencyAccount, $input) {
                $reserve = $this->estimateService->create(config('consts.reserves.RECEPTION_TYPE_ASP'), $agencyAccount, $input, config('consts.reserves.APPLICATION_STEP_DRAFT')); // 「application_step=見積」で作成

                event(new ReserveChangeRepresentativeEvent($reserve)); // 代表者変更イベント

                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント

                return $reserve;
            });

            if ($reserve) {
                return redirect()->route('staff.asp.estimates.normal.index', [$agencyAccount])->with('success_message', "「{$reserve->estimate_number}」を登録しました");
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 編集ページ
     *
     * @param string $estimateNumber 見積番号
     */
    public function edit(string $agencyAccount, string $estimateNumber)
    {
        $reserve = $this->estimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 念の為ステータスチェック
        if ($reserve->application_step != config('consts.reserves.APPLICATION_STEP_DRAFT')) {
            abort(404);
        }

        return view('staff.estimate.edit', compact('reserve'));
    }

    /**
     * 更新処理
     */
    public function update(EstimateUpdateRequest $request, string $agencyAccount, string $estimateNumber)
    {
        $reserve = $this->estimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('update', [$reserve]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        // 念の為ステータスチェック
        if ($reserve->application_step != config('consts.reserves.APPLICATION_STEP_DRAFT')) {
            abort(404);
        }

        $input = $request->all();
        try {
            $updatedReserve = DB::transaction(function () use ($reserve, $agencyAccount, $input) {
                $updatedReserve = $this->estimateService->update($reserve->id, $agencyAccount, $input);

                event(new UpdatedReserveEvent($reserve, $updatedReserve));

                return $updatedReserve;
            });

            if ($updatedReserve) {
                if ($updatedReserve->reserve_itinerary_exists && ($reserve->departure_date != $updatedReserve->departure_date || $reserve->return_date != $updatedReserve->return_date)) { // 行程が登録されていて旅行日が変わった場合はメッセージを変える
                    $successMessage = "「{$updatedReserve->estimate_number}」を更新しました。旅行日が変更されている場合は行程の更新も行ってください";
                } else {
                    $successMessage = "「{$updatedReserve->estimate_number}」を更新しました";
                }
                return redirect()->route('staff.asp.estimates.normal.index', [$agencyAccount])->with('success_message', $successMessage);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 見積状態をチェックして予約の場合は予約詳細へ転送
     */
    public function checkEstimateState(string $agencyAccount, Reserve $reserve)
    {
        if ($reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約段階に切り替わった場合は転送
            $q = '';
            if (($qp = request()->query())) { // GETクエリがある場合はパラメータもつけて転送
                $q = "?" . http_build_query($qp);
            }
            return redirect(route('staff.asp.estimates.reserve.show', [$agencyAccount, $reserve->control_number]) . $q)->throwResponse();
        }
    }
}
