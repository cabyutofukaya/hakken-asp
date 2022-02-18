<?php

namespace App\Http\Controllers\Staff\Web;

use App\Models\ReserveReceipt;
use App\Services\WebReserveService;
use App\Services\ReserveReceiptService;
use App\Services\ReserveInvoiceService;
use App\Http\Controllers\Staff\AppController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 通常請求用領収書作成・編集管理
 */
class ReserveReceiptController extends AppController
{
    public function __construct(WebReserveService $webReserveService, ReserveReceiptService $reserveReceiptService, ReserveInvoiceService $reserveInvoiceService)
    {
        $this->webReserveService = $webReserveService;
        $this->reserveReceiptService = $reserveReceiptService;
        $this->reserveInvoiceService = $reserveInvoiceService;
    }

    /**
     * @param string $agencyAccount 会社アカウント
     * @param string $reserveNumber 予約番号
     */
    public function edit(string $agencyAccount, string $reserveNumber)
    {
        $reserve = $this->webReserveService->findByControlNumber($reserveNumber, $agencyAccount);

        $reserveInvoice = $this->reserveInvoiceService->findByReserveId(data_get($reserve, 'id'));

        if (!$reserve || !$reserveInvoice) {
            abort(404);
        }

        $reserveReceipt = $this->reserveReceiptService->findByReserveInvoiceId($reserveInvoice->id);

        // 認可チェック
        if ($reserveReceipt) { // 編集時
            $response = \Gate::inspect('view', [$reserveReceipt]);
            if (!$response->allowed()) {
                abort(403);
            }
        } else { // 新規作成時
            $response = \Gate::inspect('create', new ReserveReceipt);
            if (!$response->allowed()) {
                abort(403);
            }
        }

        return view('staff.web.reserve_receipt.edit', compact('reserve', 'reserveInvoice', 'reserveReceipt', 'reserveNumber'));
    }
}
