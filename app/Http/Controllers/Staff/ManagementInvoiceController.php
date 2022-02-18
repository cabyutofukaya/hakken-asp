<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ReserveInvoice;
use App\Models\VReserveInvoice;
use App\Services\ReserveBundleInvoiceService;
use Hashids;
use Illuminate\Http\Request;

class ManagementInvoiceController extends Controller
{
    public function __construct(ReserveBundleInvoiceService $reserveBundleInvoiceService)
    {
        $this->reserveBundleInvoiceService = $reserveBundleInvoiceService;
    }

    /**
     * 請求管理一覧
     */
    public function index(string $agencyAccount)
    {
        // 認可チェック
        $response = \Gate::inspect('viewAny', [new VReserveInvoice]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.management_invoice.index');
    }

    /**
     * 一括請求内訳一覧
     *
     * @param string $reserveBundleInvoiceHashId reserve_bundle_invoice_idのハッシュ
     */
    public function breakdown(string $agencyAccount, string $reserveBundleInvoiceHashId)
    {
        // 認可チェック
        $response = \Gate::inspect('viewAny', [new ReserveInvoice]);
        if (!$response->allowed()) {
            abort(403);
        }

        $reserveBundleInvoiceId = Hashids::decode($reserveBundleInvoiceHashId)[0] ?? null;

        // 一括請求レコードを取得（転送判定に使うので論理削除を含めて取得）
        $reserveBundleInvoice = $this->reserveBundleInvoiceService->find((int)$reserveBundleInvoiceId, [], [], true);

        if ($reserveBundleInvoice->trashed()) { // 請求書の帰着日を変更した際、一括請求レコードが不要になった場合はIDも無くなるので、レコードがない場合はindexページに転送する
            return redirect()->route('staff.management.invoice.index', [$agencyAccount]);
        }

        return view('staff.management_invoice.breakdown', compact('reserveBundleInvoiceId', 'reserveBundleInvoice'));
    }
}
