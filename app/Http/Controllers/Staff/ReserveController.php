<?php

namespace App\Http\Controllers\Staff;

use App\Events\ReserveChangeHeadcountEvent;
use App\Events\ReserveChangeRepresentativeEvent;
use App\Events\ReserveEvent;
use App\Events\UpdatedReserveEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CancelChargeUpdateRequest;
use App\Http\Requests\Staff\ReserveStoretRequest;
use App\Http\Requests\Staff\ReserveUpdateRequest;
use App\Models\Reserve;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveParticipantPriceService;
use App\Services\ReserveService;
use App\Traits\ReserveControllerTrait;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Log;

class ReserveController extends AppController
{
    use ReserveControllerTrait;

    public function __construct(ReserveService $reserveService, ReserveInvoiceService $reserveInvoiceService, ReserveParticipantPriceService $reserveParticipantPriceService, ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService)
    {
        $this->reserveService = $reserveService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->reserveParticipantPriceService = $reserveParticipantPriceService;
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
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

        // 催行済みの場合は転送
        $this->checkReserveState($agencyAccount, $reserve);

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
                if ($reserve->is_departed) { // 催行済の場合は催行一覧へ
                    return redirect(route('staff.estimates.departed.index', [$agencyAccount]))->with('success_message', "「{$reserve->control_number}」を登録しました");
                } else {
                    return redirect()->route('staff.asp.estimates.reserve.index', [$agencyAccount])->with('success_message', "「{$reserve->control_number}」を登録しました");
                }
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

        // 催行済みの場合は転送
        $this->checkReserveState($agencyAccount, $reserve);

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

        // 催行済みの場合は転送
        $this->checkReserveState($agencyAccount, $reserve);

        $input = $request->all();
        try {
            $updatedReserve = DB::transaction(function () use ($reserve, $agencyAccount, $input) {
                $updatedReserve = $this->reserveService->update($reserve->id, $agencyAccount, $input);

                event(new UpdatedReserveEvent($reserve, $updatedReserve));

                return $updatedReserve;
            });

            if ($updatedReserve) {
                if ($updatedReserve->is_departed) { // 催行済の場合は催行一覧へ
                    return redirect(route('staff.estimates.departed.index', [$agencyAccount]))->with('success_message', "「{$updatedReserve->control_number}」を更新しました");
                } else {
                    return redirect()->route('staff.asp.estimates.reserve.index', [$agencyAccount])->with('success_message', "「{$updatedReserve->control_number}」を更新しました");
                }
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * キャンセルチャージ設定ページ
     */
    public function cancelCharge(string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('cancel', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 念の為、予約状態であることを確認
        if ($reserve->application_step != config('consts.reserves.APPLICATION_STEP_RESERVE')) {
            abort(404);
        }

        // 仕入の有効・無効に関係なく全ての仕入情報を引っ張る
        $purchasingList = $this->reserveParticipantPriceService->getPurchaseFormDataByReserveId($reserve->id);

        // キャンセルチャージを新たに設定(store時)する場合はis_cancelカラムはtrueで初期化
        if (!$reserve->cancel_charge) {
            foreach ($purchasingList as $key => $row) {
                $purchasingList[$key]['is_cancel'] = 1;
            }
        }

        return view('staff.reserve.cancel_charge', compact('reserve', 'purchasingList'));
    }

    /**
     * キャンセルチャージ処理
     */
    public function cancelChargeUpdate(CancelChargeUpdateRequest $request, string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('cancel', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        try {

            $input = $request->validated();

            DB::transaction(function () use ($input, $reserve) {

                // キャンセルチャージ料金を保存
                foreach ($input['rows'] as $key => $row) { // $keyは [科目名]_(仕入ID_...)という形式
                    $info = explode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), $key);
    
                    $subject = $info[0]; // $infoの1番目の配列は科目名
                    $ids = array_slice($info, 1); // idリスト

                    $cancelCharge = ($row['cancel_charge'] ?? 0) / $row['quantity']; // 数量で割って1商品あたりのキャンセルチャージを求める

                    if ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) { // オプション科目
                        $this->reserveParticipantOptionPriceService->setCancelChargeByIds($cancelCharge, Arr::get($row, 'is_cancel') == 1, $ids);
    
                    } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) { // 航空券科目
                        $this->reserveParticipantAirplanePriceService->setCancelChargeByIds($cancelCharge, Arr::get($row, 'is_cancel') == 1, $ids);
    
                    } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) { // ホテル科目
                        $this->reserveParticipantHotelPriceService->setCancelChargeByIds($cancelCharge, Arr::get($row, 'is_cancel') == 1, $ids);
    
                    }
                }
                
                $this->reserveService->cancel($reserve->id, true);
            });

            // TODO リダイレクト先はひとまず予約詳細ページにしているが、変更する可能性あり
            return redirect()->route('staff.asp.estimates.reserve.show', [$agencyAccount, $controlNumber])->with('success_message', "「{$controlNumber}」のキャンセルチャージ処理が完了しました");

        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);

    }
}
