<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveBundleInvoiceUpdateRequest;
use App\Http\Resources\Staff\ReserveBundleInvoice\BreakdownResource;
use App\Http\Resources\Staff\ReserveBundleInvoice\UpdateResource;
use App\Models\ReserveInvoice;
use App\Services\ReserveBundleInvoiceService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveService;
use App\Services\AgencyDepositService;
use Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ReserveBundleInvoiceController extends Controller
{
    public function __construct(ReserveService $reserveService, ReserveInvoiceService $reserveInvoiceService, ReserveBundleInvoiceService $reserveBundleInvoiceService, AgencyDepositService $agencyDepositService)
    {
        $this->reserveService = $reserveService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->reserveBundleInvoiceService = $reserveBundleInvoiceService;
        $this->agencyDepositService = $agencyDepositService;
    }

    /**
     * 作成or更新
     *
     * @param int $reserveBundleInvoiceId reserve_bundle_invoice_id
     */
    public function edit(ReserveBundleInvoiceUpdateRequest $request, string $agencyAccount, int $reserveBundleInvoiceId)
    {
        $reserveBundleInvoice = $this->reserveBundleInvoiceService->find($reserveBundleInvoiceId);

        if (!$reserveBundleInvoice) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = \Gate::authorize('update', $reserveBundleInvoice);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $agencyId = auth('staff')->user()->agency_id;

        $input = $request->all();
        $input['agency_id'] = $agencyId;
        
        try {
            $newReserveBundleInvoice = \DB::transaction(function () use ($reserveBundleInvoiceId, $input, $reserveBundleInvoice) {
                $newReserveBundleInvoice =  $this->reserveBundleInvoiceService->update($reserveBundleInvoiceId, $input);

                return $newReserveBundleInvoice;
            });
            if ($newReserveBundleInvoice) {
                if (request()->input("create_pdf")) { // PDF作成
                    $pdfFile = $this->reserveBundleInvoiceService->createPdf('staff.reserve_bundle_invoice.pdf', ['reserveBundleInvoice' => $newReserveBundleInvoice]);

                    // 作成したPDFファイル名をセット
                    $this->reserveBundleInvoiceService->setPdf($newReserveBundleInvoice, $pdfFile, $agencyId);
                }
                if (request()->input("set_message")) {
                    request()->session()->flash(
                        'success_message',
                        $newReserveBundleInvoice->user_bundle_invoice_number ? "「一括請求書({$newReserveBundleInvoice->user_bundle_invoice_number})の保存処理が完了しました。" : "一括請求書の保存処理が完了しました。"
                    ); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return new UpdateResource($this->reserveBundleInvoiceService->find($newReserveBundleInvoice->id), 201);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー（保存とpdf出力を同時に行う場所があるので、保存時した内容とpdfの内容が一致していることを担保する意味でもチェック）
            abort(409, "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }

    /**
     * 当該reserve_bundle_invoice_idに紐づく内訳一覧を取得
     *
     * @param int $reserveBundleInvoiceId 一括請求ID
     */
    public function breakdownOfBundle(string $agencyAccount, int $reserveBundleInvoiceId)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new ReserveInvoice);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return BreakdownResource::collection(
            $this->reserveInvoiceService->paginateByReserveBundleInvoiceId(
                $agencyAccount,
                $reserveBundleInvoiceId,
                request()->get("per_page", 10),
                ['reserve',],
                [],
                false
            )
        );
    }
}
