<?php

namespace App\Http\Controllers\Staff\Api;

use App\Events\AgencyDepositedEvent;
use App\Events\AgencyDepositChangedEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveInvoiceDepositBatchRequest;
use App\Http\Requests\Staff\ReserveInvoiceUpsertRequest;
use App\Http\Resources\Staff\ReserveBundleInvoice\BreakdownResource;
use App\Http\Resources\Staff\ReserveInvoice\UpdateResource;
use App\Http\Resources\Staff\VReserveInvoice\IndexResource;
use App\Models\ReserveInvoice;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveService;
use App\Services\WebReserveService;
use App\Services\AgencyDepositService;
use App\Services\VReserveInvoiceService;
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
        $response = \Gate::authorize('update', $reserve);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $agencyId = auth('staff')->user()->agency_id;
        $reserveId = $reserve->id;

        $input = $request->all();
        $input['agency_id'] = $agencyId;

        try {
            $newReserveInvoice = \DB::transaction(function () use ($agencyId, $reserveId, $input) {
                $oldReserveInvoice = $this->reserveInvoiceService->findByReserveId($reserveId);
                
                $newReserveInvoice = $this->reserveInvoiceService->upsert($agencyId, $reserveId, $input);

                return $newReserveInvoice;
            });
            if ($newReserveInvoice) {
                if (request()->input("create_pdf")) { // PDF作成
                    $viewPath = '';
                    // 受付種別で分ける
                    if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                        $viewPath = 'staff.reserve_invoice.pdf';
                    } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                        $viewPath = 'staff.web.reserve_invoice.pdf';
                    }
                    $pdfFile = $this->reserveInvoiceService->createPdf($viewPath, ['reserveInvoice' => $newReserveInvoice]);

                    // 作成したPDFファイル名をセット
                    $this->reserveInvoiceService->setPdf($newReserveInvoice, $pdfFile, $agencyId);
                }
                if (request()->input("set_message")) {
                    request()->session()->flash('success_message', "「請求書({$newReserveInvoice->user_invoice_number})の保存処理が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return new UpdateResource($this->reserveInvoiceService->find($newReserveInvoice->id), 201);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー（保存とpdf出力を同時に行う場所があるので、保存時した内容とpdfの内容が一致していることを担保する意味でもチェック）
            abort(409, "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
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

                // 編集権限をチェック
                $response = \Gate::authorize('update', $reserveInvoice);
                if (!$response->allowed()) {
                    abort(403, $response->message());
                }

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
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }
}
