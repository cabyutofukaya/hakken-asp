<?php

namespace App\Http\Controllers\Staff\Web;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Staff\AppController;
use App\Http\Requests\Staff\EstimateUpdateRequest;
use App\Models\Reserve;
use App\Events\UpdatedReserveEvent;
use App\Services\WebEstimateService;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;

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
        $response = Gate::inspect('viewAny', [new Reserve]);
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
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 相談ステータスのページなので念の為、ステータスもチェック
        if ($reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約段階に切り替わった場合は転送
            // TODO URLが決まったら実装
        } elseif ($reserve->application_step == config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 見積段階に切り替わった場合は転送
            return redirect(route('staff.web.estimates.normal.show', [$agencyAccount, $reserve->estimate_number]));
        }

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
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 見積ステータスのページなので念の為、ステータスもチェック
        if ($reserve->application_step != config('consts.reserves.APPLICATION_STEP_DRAFT')) {
            abort(404);
        }

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
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
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
        $response = Gate::inspect('update', [$reserve]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all();
        try {
            $updatedReserve = DB::transaction(function () use ($reserve, $agencyAccount, $input) {
                $updatedReserve = $this->webEstimateService->update($reserve->id, $agencyAccount, $input);

                event(new UpdatedReserveEvent($reserve, $updatedReserve));

                return $updatedReserve;
            });

            if ($updatedReserve) {
                return redirect()->route('staff.web.estimates.normal.index', [$agencyAccount])->with('success_message', "「{$updatedReserve->estimate_number}」を更新しました");
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }
}
