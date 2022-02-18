<?php

namespace App\Http\Controllers\Staff;

use App\Events\UpdatedReserveEvent;
use App\Events\ReserveChangeHeadcountEvent;
use App\Events\ReserveChangeRepresentativeEvent;
use App\Events\ReserveEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveStoretRequest;
use App\Http\Requests\Staff\ReserveUpdateRequest;
use App\Models\Reserve;
use App\Services\ReserveService;
use App\Services\ReserveInvoiceService;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;

class ReserveController extends AppController
{
    public function __construct(ReserveService $reserveService, ReserveInvoiceService $reserveInvoiceService)
    {
        $this->reserveService = $reserveService;
        $this->reserveInvoiceService = $reserveInvoiceService;
    }

    public function index()
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new Reserve]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        return view('staff.reserve.index');
    }

    /**
     * 詳細表示ページ
     *
     * @param string $controlNumber 予約番号
     */
    public function show($agencyAccount, $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.reserve.show', compact('reserve'));
    }

    /**
     * 新規予約作成ページ
     */
    public function create()
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        $applicationStep = config('consts.reserves.APPLICATION_STEP_RESERVE'); // 申込段階。CreateFormComposer内で見積と予約で処理を分ける際に使用する変数

        return view('staff.reserve.create', compact('applicationStep'));
    }

    /**
     * 新規予約作成処理
     */
    public function store(ReserveStoretRequest $request, $agencyAccount)
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
                $reserve = $this->reserveService->create(config('consts.reserves.RECEPTION_TYPE_ASP'), $agencyAccount, $input, config('consts.reserves.APPLICATION_STEP_RESERVE'));

                event(new ReserveChangeRepresentativeEvent($reserve)); // 代表者変更イベント

                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント

                event(new ReserveEvent($reserve)); // 予約作成イベント

                return $reserve;
            });

            if ($reserve) {
                return redirect()->route('staff.asp.estimates.reserve.index', [$agencyAccount])->with('success_message', "「{$reserve->control_number}」を登録しました");
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 編集ページ
     *
     * @param string $controlNumber 予約番号
     */
    public function edit(string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.reserve.edit', compact('reserve'));
    }

    /**
     * 更新処理
     */
    public function update(ReserveUpdateRequest $request, string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('update', [$reserve]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all();
        try {
            $updatedReserve = DB::transaction(function () use ($reserve, $agencyAccount, $input) {
                $updatedReserve = $this->reserveService->update($reserve->id, $agencyAccount, $input);

                event(new UpdatedReserveEvent($reserve, $updatedReserve));

                return $updatedReserve;
            });

            if ($updatedReserve) {
                return redirect()->route('staff.asp.estimates.reserve.index', [$agencyAccount])->with('success_message', "「{$updatedReserve->control_number}」を更新しました");
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }
}
