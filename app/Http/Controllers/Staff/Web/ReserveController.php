<?php

namespace App\Http\Controllers\Staff\Web;

use App\Exceptions\ExclusiveLockException;
use App\Models\Reserve;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Staff\AppController;
use Illuminate\Http\Request;
use App\Events\UpdatedReserveEvent;
use App\Http\Requests\Staff\ReserveUpdateRequest;
use App\Services\WebReserveService;
use App\Services\ReserveInvoiceService;
use Gate;

/**
 * 予約管理
 */
class ReserveController extends AppController
{
    public function __construct(WebReserveService $webReserveService, ReserveInvoiceService $reserveInvoiceService)
    {
        $this->webReserveService = $webReserveService;
        $this->reserveInvoiceService = $reserveInvoiceService;
    }

    /**
     * 予約一覧ページ
     */
    public function index()
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new Reserve]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        return view('staff.web.reserve.index');
    }

    /**
     * 詳細表示ページ
     *
     * @param string $controlNumber 予約番号
     */
    public function show($agencyAccount, $controlNumber)
    {
        $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.web.reserve.show', compact('reserve'));
    }

    /**
     * 編集ページ
     *
     * @param string $controlNumber 予約番号
     */
    public function edit(string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.web.reserve.edit', compact('reserve'));
    }

    /**
     * 更新処理
     */
    public function update(ReserveUpdateRequest $request, string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('update', [$reserve]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all();
        try {
            $updatedReserve = \DB::transaction(function () use ($reserve, $agencyAccount, $input) {
                $updatedReserve = $this->webReserveService->update($reserve->id, $agencyAccount, $input);

                event(new UpdatedReserveEvent($reserve, $updatedReserve));

                return $updatedReserve;
            });

            if ($updatedReserve) {
                return redirect()->route('staff.web.estimates.reserve.index', [$agencyAccount])->with('success_message', "「{$updatedReserve->control_number}」を更新しました");
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }
}
