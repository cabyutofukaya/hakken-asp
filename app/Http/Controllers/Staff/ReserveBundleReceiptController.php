<?php

namespace App\Http\Controllers\Staff;

use App\Models\ReserveBundleReceipt;
use App\Services\ReserveService;
use App\Services\ReserveBundleReceiptService;
use App\Services\ReserveBundleInvoiceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Hashids;

/**
 * 通常請求用領収書作成・編集管理
 */
class ReserveBundleReceiptController extends AppController
{
    public function __construct(ReserveService $reserveService, ReserveBundleReceiptService $reserveBundleReceiptService, ReserveBundleInvoiceService $reserveBundleInvoiceService)
    {
        $this->reserveService = $reserveService;
        $this->reserveBundleReceiptService = $reserveBundleReceiptService;
        $this->reserveBundleInvoiceService = $reserveBundleInvoiceService;
    }

    /**
     * @param string $agencyAccount 会社アカウント
     * @param string $reserveBundleInvoiceHashId 一括請求ID(ハッシュ)
     */
    public function edit(string $agencyAccount, string $reserveBundleInvoiceHashId)
    {
        $reserveBundleInvoiceId = Hashids::decode($reserveBundleInvoiceHashId)[0] ?? null;

        $reserveBundleInvoice = $this->reserveBundleInvoiceService->find($reserveBundleInvoiceId);

        $reserveBundleReceipt = $this->reserveBundleReceiptService->findByReserveBundleInvoiceId($reserveBundleInvoiceId);

        // 認可チェック
        if ($reserveBundleReceipt) { // 編集時
            $response = \Gate::inspect('view', [$reserveBundleReceipt]);
            if (!$response->allowed()) {
                abort(403);
            }
        } else { // 新規作成時
            $response = \Gate::inspect('create', new ReserveBundleReceipt);
            if (!$response->allowed()) {
                abort(403);
            }
        }

        return view('staff.reserve_bundle_receipt.edit', compact('reserveBundleReceipt', 'reserveBundleInvoice', 'reserveBundleInvoiceHashId'));
    }
}
