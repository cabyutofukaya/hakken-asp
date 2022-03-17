<?php

namespace App\Http\Controllers\Staff\Api;

use App\Events\AgencyDepositChangedEvent;
use App\Events\AgencyDepositedEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveInvoiceDepositBatchRequest;
use App\Http\Requests\Staff\ReserveInvoiceUpsertRequest;
use App\Http\Requests\Staff\ReserveInvoiceStatusUpdateRequest;
use App\Http\Resources\Staff\ReserveBundleInvoice\BreakdownResource;
use App\Http\Resources\Staff\ReserveInvoice\StatusUpdateResource;
use App\Http\Resources\Staff\ReserveInvoice\UpdateResource;
use App\Http\Resources\Staff\VReserveInvoice\IndexResource;
use App\Models\AgencyDeposit;
use App\Models\ReserveInvoice;
use App\Services\AgencyDepositService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveService;
use App\Services\VReserveInvoiceService;
use App\Services\WebReserveService;
use Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ReserveInvoiceController extends Controller
{
    public function __construct(ReserveService $reserveService, WebReserveService $webReserveService, ReserveInvoiceService $reserveInvoiceService, AgencyDepositService $agencyDepositService, VReserveInvoiceService $vReserveInvoiceService)
    {
        $this->reserveService = $reserveService;
        $this->webReserveService = $webReserveService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->agencyDepositService = $agencyDepositService;
        $this->vReserveInvoiceService = $vReserveInvoiceService;
    }

    /**
     * 作成or更新
     */
    public function upsert(ReserveInvoiceUpsertRequest $request, $agencyAccount, string $reception, $reserveNumber)
    {
        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            $reserve = $this->webReserveService->findByControlNumber($reserveNumber, $agencyAccount);
        } else {
            abort(404);
        }

        if (!$reserve) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        if ($reserve->reserve_invoice) { // 編集時
            $response = \Gate::inspect('update', [$reserve->reserve_invoice]);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }
        } else { // 新規作成時
            $response = \Gate::inspect('create', new ReserveInvoice);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }
        }

        // // 認可チェック
        // $response = \Gate::authorize('update', $reserve);
        // if (!$response->allowed()) {
        //     abort(403, $response->message());
        // }

        $agencyId = auth('staff')->user()->agency_id;

        $input = $request->all();
        $input['agency_id'] = $agencyId;

        try {
            $reserveInvoice = \DB::transaction(function () use ($agencyId, $reserve, $input) {
                if ($reserve->updated_at != Arr::get($input, 'reserve.updated_at')) { // キャンセルなどの予約ステータスを考慮する必要があるので予約レコードの更新日時で同時編集チェック
                    throw new ExclusiveLockException;
                }

                $reserveInvoice = $this->reserveInvoiceService->upsert($agencyId, $reserve->id, $reserve->enabled_reserve_itinerary->id, $input);

                return $reserveInvoice;
            });
            if ($reserveInvoice) {
                if (request()->input("create_pdf")) { // PDF作成
                    $viewPath = '';
                    // 受付種別で分ける
                    if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                        $viewPath = 'staff.reserve_invoice.pdf';
                    } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                        $viewPath = 'staff.web.reserve_invoice.pdf';
                    }
                    $pdfFile = $this->reserveInvoiceService->createPdf($viewPath, ['reserveInvoice' => $reserveInvoice]);

                    // 作成したPDFファイル名をセット
                    $this->reserveInvoiceService->setPdf($reserveInvoice, $pdfFile, $agencyId);
                }
                if (request()->input("set_message")) {
                    request()->session()->flash('success_message', "「請求書({$reserveInvoice->user_invoice_number})の保存処理が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return new UpdateResource($this->reserveInvoiceService->find($reserveInvoice->id), 201);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー（保存とpdf出力を同時に行う場所があるので、保存時した内容とpdfの内容が一致していることを担保する意味でもチェック）
            abort(409, "予約情報が更新されています。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }

    /**
     * 一括入金処理
     */
    public function depositBatch(ReserveInvoiceDepositBatchRequest $request, string $agencyAccount)
    {
        $input = $request->all(); //カスタムフィールドがあるのでallで取得
        $input['input']['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット

        // id=>updated_at形式の配列にまとめる
        $idInfo = collect($input['data'])->pluck('updated_at', 'id')->toArray();

        $reserveInvoices = $this->reserveInvoiceService->getByIds(array_keys($idInfo), ['agency_deposits'], [], false);

        try {
            foreach ($reserveInvoices as $reserveInvoice) {

                // reserve_invoicesを使い、対象請求が操作ユーザー会社所有データであることも確認。try文内では例外が投げられてしまうので認可エラーはcatch文で処理
                \Gate::authorize('create', [new AgencyDeposit, $reserveInvoice]);

                $data = $input['input'];
                $data['reserve_invoice'] = [];

                $data['amount'] = $reserveInvoice->sum_not_deposit; // 未入金合計をデポジット
                $data['reserve_id'] = $reserveInvoice->reserve_id; // 予約ID
                $data['reserve_invoice_id'] = $reserveInvoice->id; // 請求書ID
                $data['reserve_invoice']['updated_at'] = Arr::get($idInfo, $reserveInvoice->id); // 請求書更新日時(同時編集チェック用)

                \DB::transaction(function () use ($data) {
                    $agencyDeposit = $this->agencyDepositService->create($data, true, true);

                    // 入金額変更処理。入金済・未入金残高計算等
                    event(new AgencyDepositChangedEvent($agencyDeposit->reserve_invoice));

                    event(new AgencyDepositedEvent($agencyDeposit));
                });
            }

            // リクエスト元に応じてレスポンスを出し分け
            if (Arr::get($input, 'list_type') === config('consts.reserve_invoices.LIST_TYPE_BREAKDOWN')) {
                // 一括請求 内訳一覧データを返す
                return BreakdownResource::collection(
                    $this->reserveInvoiceService->paginateByReserveBundleInvoiceId(
                        $agencyAccount,
                        Arr::get($input, 'reserve_bundle_invoice_id'),
                        $request->get("per_page", 10),
                        ['reserve',],
                        [],
                        false
                    )
                );
            } elseif (Arr::get($input, 'list_type') === config('consts.reserve_invoices.LIST_TYPE_INDEX')) { // 請求管理indexリスト
                return IndexResource::collection(
                    $this->vReserveInvoiceService->paginateByAgencyAccount(
                        $agencyAccount,
                        Arr::get($input, 'params'),
                        request()->get("per_page", 10),
                        [
                            'reserve',
                            'agency_bundle_deposits.v_agency_bundle_deposit_custom_values',
                            'agency_deposits.v_agency_deposit_custom_values',
                        ]
                    )
                );
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return response($e->getMessage(), 409);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response($e->getMessage(), 403);
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * ステータス更新
     *
     * @param int $reserveInvoiceId 請求書ID
     */
    public function statusUpdate(ReserveInvoiceStatusUpdateRequest $request, $agencyAccount, int $reserveInvoiceId)
    {
        $reserveInvoice = $this->reserveInvoiceService->find($reserveInvoiceId);
        
        if (!$reserveInvoice) {
            abort(404, "請求データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $response = \Gate::authorize('update', $reserveInvoice);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            // 書類更新時に予約レコードの更新日時をチェックしているので、一応ここでもチェック
            if ($reserveInvoice->reserve->updated_at != $input['reserve']['updated_at']) {
                throw new ExclusiveLockException;
            }

            if ($this->reserveInvoiceService->updateStatus($reserveInvoiceId, $input['status'])) {
                return new StatusUpdateResource($this->reserveInvoiceService->find($reserveInvoiceId));
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "予約情報が更新されています。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }
}
