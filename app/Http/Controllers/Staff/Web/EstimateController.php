<?php

namespace App\Http\Controllers\Staff\Web;

use App\Events\UpdatedReserveEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Staff\AppController;
use App\Http\Requests\Staff\EstimateUpdateRequest;
use App\Models\Reserve;
use App\Services\WebEstimateService;
use Illuminate\Http\Request;

/**
 * 見積管理
 */
class EstimateController extends AppController
{
    public function __construct(
        WebEstimateService $webEstimateService
    ) {
        $this->webEstimateService = $webEstimateService;
    }

    // index
    public function index()
    {
        // 認可チェック
        $response = \Gate::inspect('viewAny', [new Reserve]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        return view('staff.web.estimate.index');
    }

    /**
     * 相談リクエスト詳細
     *
     * @param string $requestNumber 依頼番号
     */
    public function request(Request $request, string $agencyAccount, string $requestNumber)
    {
        $reserve = $this->webEstimateService->findByRequestNumber($requestNumber, $agencyAccount, ['web_reserve_ext.web_consult']);

        if (!$reserve) {
            abort(404);
        }

        // 認可チェック
        $response = \Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // リクエスト状態をチェックして必要に応じて転送処理
        $this->checkRequestState($agencyAccount, $reserve);

        if ($reserve->application_step != config('consts.reserves.APPLICATION_STEP_CONSULT')) {
            abort(404);
        }

        return view('staff.web.estimate.request', compact('reserve'));
    }

    /**
     * 見積詳細ページ
     *
     * @param string $estimateNumber 見積番号
     */
    public function show(string $agencyAccount, string $estimateNumber)
    {
        $reserve = $this->webEstimateService->findByEstimateNumber($estimateNumber, $agencyAccount, ['web_reserve_ext.web_consult','web_reserve_ext.web_online_schedule']);

        if (!$reserve) {
            abort(404);
        }

        // 認可チェック
        $response = \Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 見積状態をチェックして必要に応じて転送処理
        $this->checkEstimateState($agencyAccount, $reserve);

        return view('staff.web.estimate.show', compact('reserve'));
    }

    /**
     * 編集ページ
     *
     * @param string $estimateNumber 見積番号
     */
    public function edit(string $agencyAccount, string $estimateNumber)
    {
        $reserve = $this->webEstimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        // 認可チェック
        $response = \Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 念の為ステータスチェック
        if ($reserve->application_step != config('consts.reserves.APPLICATION_STEP_DRAFT')) {
            abort(404);
        }

        return view('staff.web.estimate.edit', compact('reserve'));
    }

    /**
     * 更新処理
     */
    public function update(EstimateUpdateRequest $request, string $agencyAccount, string $estimateNumber)
    {
        $reserve = $this->webEstimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        // 認可チェック
        $response = \Gate::inspect('update', [$reserve]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all();
        try {
            $updatedReserve = \DB::transaction(function () use ($reserve, $agencyAccount, $input) {
                $updatedReserve = $this->webEstimateService->update($reserve->id, $agencyAccount, $input);

                event(new UpdatedReserveEvent($reserve, $updatedReserve));

                return $updatedReserve;
            });

            if ($updatedReserve) {
                if ($updatedReserve->reserve_itinerary_exists && ($reserve->departure_date != $updatedReserve->departure_date || $reserve->return_date != $updatedReserve->return_date)) { // 行程が登録されていて旅行日が変わった場合はメッセージを変える
                    $successMessage = "「{$updatedReserve->estimate_number}」を更新しました。旅行日が変更されている場合は行程の更新も行ってください";
                } else {
                    $successMessage = "「{$updatedReserve->estimate_number}」を更新しました";
                }
                return redirect()->route('staff.web.estimates.normal.index', [$agencyAccount])->with('success_message', $successMessage);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * リクエスト状態をチェックして必要に応じて見積/予約詳細ページへ転送
     */
    public function checkRequestState(string $agencyAccount, Reserve $reserve)
    {
        $q = '';
        if (($qp = request()->query())) { // GETクエリがある場合はパラメータもつけて転送
            $q = "?" . http_build_query($qp);
        }

        if ($reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約段階に切り替わった場合は転送
            return redirect(route('staff.web.estimates.reserve.show', [$agencyAccount, $reserve->control_number]) . $q)->throwResponse();
        } elseif ($reserve->application_step == config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 見積段階に切り替わった場合は転送
            return redirect(route('staff.web.estimates.normal.show', [$agencyAccount, $reserve->estimate_number]). $q)->throwResponse();
        }
    }

    /**
     * 見積状態をチェックして必要に応じて予約詳細ページへ転送
     */
    public function checkEstimateState(string $agencyAccount, Reserve $reserve)
    {
        $q = '';
        if (($qp = request()->query())) { // GETクエリがある場合はパラメータもつけて転送
            $q = "?" . http_build_query($qp);
        }

        if ($reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約段階に切り替わった場合は転送
            return redirect(route('staff.web.estimates.reserve.show', [$agencyAccount, $reserve->control_number]) . $q)->throwResponse();
        }
    }
}
