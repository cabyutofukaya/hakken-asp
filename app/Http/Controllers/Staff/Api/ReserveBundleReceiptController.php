<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveBundleReceiptStatusUpdateRequest;
use App\Http\Requests\Staff\ReserveBundleReceiptUpsertRequest;
use App\Http\Resources\Staff\ReserveBundleReceipt\StatusUpdateResource;
use App\Http\Resources\Staff\ReserveBundleReceipt\UpdateResource;
use App\Models\ReserveBundleReceipt;
use App\Services\ReserveBundleReceiptService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveService;
use Hashids;
use Illuminate\Http\Request;

class ReserveBundleReceiptController extends Controller
{
    public function __construct(ReserveService $reserveService, ReserveBundleReceiptService $reserveBundleReceiptService, ReserveInvoiceService $reserveInvoiceService)
    {
        $this->reserveService = $reserveService;
        $this->reserveBundleReceiptService = $reserveBundleReceiptService;
        $this->reserveInvoiceService = $reserveInvoiceService;
    }

    /**
     * 作成or更新
     *
     * @param string $reserveBundleInvoiceHashId 一括請求書ID(ハッシュ)
     */
    public function upsert(ReserveBundleReceiptUpsertRequest $request, string $agencyAccount, string $reserveBundleInvoiceHashId)
    {
        $reserveBundleInvoiceId = Hashids::decode($reserveBundleInvoiceHashId)[0] ?? null;

        $reserveBundleReceipt = $this->reserveBundleReceiptService->findByReserveBundleInvoiceId($reserveBundleInvoiceId);

        // 認可チェック
        if ($reserveBundleReceipt) {
            $response = \Gate::authorize('update', $reserveBundleReceipt);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }
        } else {
            $response = \Gate::authorize('create', new ReserveBundleReceipt);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }
        }

        $input = $request->all();
        $input['agency_id'] = auth('staff')->user()->agency_id;
        $input['reserve_bundle_invoice_id'] = $reserveBundleInvoiceId;

        try {
            $newReserveBundleReceipt = \DB::transaction(function () use ($reserveBundleReceipt, $input) {
                return $this->reserveBundleReceiptService->upsert($reserveBundleReceipt, $input);
            });
            if ($newReserveBundleReceipt) {
                if (request()->input("create_pdf")) { // PDF作成
                    $pdfFile = $this->reserveBundleReceiptService->createPdf('staff.reserve_bundle_receipt.pdf', ['reserveBundleReceipt' => $newReserveBundleReceipt]);

                    // 作成したPDFファイル名をセット
                    $this->reserveBundleReceiptService->setPdf($newReserveBundleReceipt, $pdfFile, $newReserveBundleReceipt->agency_id);
                }
                if (request()->input("set_message")) {
                    request()->session()->flash('success_message', "「領収書({$newReserveBundleReceipt->user_receipt_number})の保存処理が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return new UpdateResource($this->reserveBundleReceiptService->find($newReserveBundleReceipt->id), 201);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー（保存とpdf出力を同時に行う場所があるので、保存時した内容とpdfの内容が一致していることを担保する意味でもチェック）
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }

    /**
     * ステータス更新
     *
     * @param int $reserveBundleReceiptId 一括領収書ID
     */
    public function statusUpdate(ReserveBundleReceiptStatusUpdateRequest $request, $agencyAccount, int $reserveBundleReceiptId)
    {
        $reserveBundleReceipt = $this->reserveBundleReceiptService->find($reserveBundleReceiptId);
        
        if (!$reserveBundleReceipt) {
            abort(404, "領収書データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        $response = \Gate::authorize('update', $reserveBundleReceipt);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            // 書類更新時に更新日時をチェックしているので、一応ここでもチェック
            if ($reserveBundleReceipt->updated_at != $input['updated_at']) {
                throw new ExclusiveLockException;
            }

            if ($this->reserveBundleReceiptService->updateStatus($reserveBundleReceiptId, $input['status'])) {
                return new StatusUpdateResource($this->reserveBundleReceiptService->find($reserveBundleReceiptId));
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }
}
