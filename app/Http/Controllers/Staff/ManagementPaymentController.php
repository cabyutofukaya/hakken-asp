<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AccountPayableDetail;
use App\Models\AccountPayableItem;
use App\Models\AccountPayableReserve;
use App\Services\ReserveBaseService;
use Illuminate\Http\Request;

class ManagementPaymentController extends AppController
{
    public function __construct(ReserveBaseService $reserveBaseService)
    {
        $this->reserveBaseService = $reserveBaseService;
    }

    /**
     * 予約毎一覧
     */
    public function reserve()
    {
        // 認可チェック
        $response = \Gate::inspect('viewAny', [new AccountPayableReserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.management_payment.reserve');
    }

    /**
     * 仕入先＆商品毎詳細
     */
    public function item(string $agencyAccount, string $reserveHashId)
    {
        // 認可チェック
        $response = \Gate::inspect('viewAny', [new AccountPayableItem]);
        if (!$response->allowed()) {
            abort(403);
        }

        if (!($reserveId = \Hashids::decode($reserveHashId)[0] ?? null)) {
            abort(404);
        }

        $reserve = $this->reserveBaseService->findForAgencyId($reserveId, auth("staff")->user()->agency_id);

        return view('staff.management_payment.item', compact("reserveHashId", "reserve"));
        // return view('staff.management_payment.item2');
    }
    
    // 商品詳細
    public function index()
    {
        // 認可チェック
        $response = \Gate::inspect('viewAny', [new AccountPayableDetail]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.management_payment.index');
    }
}
