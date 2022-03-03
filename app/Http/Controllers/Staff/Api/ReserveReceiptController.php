<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveReceiptUpsertRequest;
use App\Http\Resources\Staff\ReserveReceipt\UpdateResource;
use App\Models\ReserveReceipt;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveReceiptService;
use App\Services\ReserveService;
use App\Services\WebReserveService;
use Illuminate\Http\Request;

class ReserveReceiptController extends Controller
{
    public function __construct(ReserveService $reserveService, WebReserveService $webReserveService, ReserveReceiptService $reserveReceiptService, ReserveInvoiceService $reserveInvoiceService)
    {
        $this->reserveService = $reserveService;
        $this->webReserveService = $webReserveService;
        $this->reserveReceiptService = $reserveReceiptService;
        $this->reserveInvoiceService = $reserveInvoiceService;
    }

    /**
     * 作成or更新
     */
    public function upsert(ReserveReceiptUpsertRequest $request, $agencyAccount, string $reception, $reserveNumber)
    {
        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);

        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            $reserve = $this->webReserveService->findByControlNumber($reserveNumber, $agencyAccount);

        } else {
            abort(404);
        }

        $reserveInvoice = $this->reserveInvoiceService->findByReserveId(data_get($reserve, 'id'));

        if (!$reserve || !$reserveInvoice) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $reserveReceipt = $this->reserveReceiptService->findByReserveInvoiceId($reserveInvoice->id);

        // 認可チェック
        if ($reserveReceipt) {
            $response = \Gate::authorize('update', $reserveReceipt);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }
        } else {
            $response = \Gate::authorize('create', new ReserveReceipt);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }
        }

        $input = $request->all();
        $input['agency_id'] = auth('staff')->user()->agency_id;
        $input['reserve_id'] = $reserve->id;
        $input['reserve_invoice_id'] = $reserveInvoice->id;

        try {
            $newReserveReceipt = \DB::transaction(function () use ($reserveReceipt, $input) {
                return $this->reserveReceiptService->upsert($reserveReceipt, $input);
            });
            if ($newReserveReceipt) {
                if (request()->input("create_pdf")) { // PDF作成
                    $viewPath = '';
                    // 受付種別で分ける
                    if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                        $viewPath = 'staff.reserve_receipt.pdf';
                    } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                        $viewPath = 'staff.web.reserve_receipt.pdf';
                    }

                    $pdfFile = $this->reserveReceiptService->createPdf($viewPath, ['reserveReceipt' => $newReserveReceipt]);

                    // 作成したPDFファイル名をセット
                    $this->reserveReceiptService->setPdf($newReserveReceipt, $pdfFile, $newReserveReceipt->agency_id);
                }
                if (request()->input("set_message")) {
                    request()->session()->flash('success_message', "「領収書({$newReserveReceipt->user_receipt_number})の保存処理が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return new UpdateResource($this->reserveReceiptService->find($newReserveReceipt->id), 201);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー（保存とpdf出力を同時に行う場所があるので、保存時した内容とpdfの内容が一致していることを担保する意味でもチェック）
            abort(409, "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }
}
