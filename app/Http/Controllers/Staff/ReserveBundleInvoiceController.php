<?php

namespace App\Http\Controllers\Staff;

use App\Models\ReserveBundleInvoice;
use App\Http\Controllers\Controller;
use App\Services\ReserveBundleInvoiceService;
use Illuminate\Http\Request;
use Hashids;

/**
 * 一括請求書作成・編集
 */
class ReserveBundleInvoiceController extends AppController
{
    public function __construct(ReserveBundleInvoiceService $reserveBundleInvoiceService)
    {
        $this->reserveBundleInvoiceService = $reserveBundleInvoiceService;
    }

    /**
     * 新規or編集
     * 
     * @param string $agencyAccount 会社アカウント
     * @param string $reserveBundleInvoiceHashId 一括請求書ID
     */
    public function edit(string $agencyAccount, string $reserveBundleInvoiceHashId)
    {
        $id = Hashids::decode($reserveBundleInvoiceHashId)[0] ?? null;

        $reserveBundleInvoice = $this->reserveBundleInvoiceService->find($id);

        // 認可チェック
        $response = \Gate::inspect('view', [$reserveBundleInvoice]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.reserve_bundle_invoice.edit', compact('reserveBundleInvoice'));
    }
}
