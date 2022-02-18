<?php

namespace App\Http\Controllers\Staff;

use App\Models\ReserveInvoice;
use App\Http\Controllers\Controller;
use App\Services\ReserveService;
use App\Services\ReserveInvoiceService;
use Illuminate\Http\Request;

/**
 * 請求書作成・編集
 */
class ReserveInvoiceController extends AppController
{
    public function __construct(ReserveService $reserveService, ReserveInvoiceService $reserveInvoiceService)
    {
        $this->reserveService = $reserveService;
        $this->reserveInvoiceService = $reserveInvoiceService;
    }

    /**
     * @param string $agencyAccount 会社アカウント
     * @param string $reserveNumber 予約番号
     */
    public function edit(string $agencyAccount, string $reserveNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);

        $reserveInvoice = $this->reserveInvoiceService->findByReserveId(data_get($reserve, 'id'));
        
        // 認可チェック
        if ($reserveInvoice) { // 編集時
            $response = \Gate::inspect('view', [$reserveInvoice]);
            if (!$response->allowed()) {
                abort(403);
            }
        } else { // 新規作成時（請求書は自動生成されるのでユーザーアクセスで本分岐に来ることはないが一応）
            $response = \Gate::inspect('create', new ReserveInvoice);
            if (!$response->allowed()) {
                abort(403);
            }
        }

        return view('staff.reserve_invoice.edit', compact('reserve', 'reserveInvoice', 'reserveNumber'));
    }
}
