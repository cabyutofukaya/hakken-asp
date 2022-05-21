<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AccountPayableDetail;
use App\Models\AccountPayableItem;
use App\Models\AccountPayableReserve;
use App\Services\ReserveBaseService;
use App\Services\SubjectAirplaneService;
use App\Services\SubjectHotelService;
use App\Services\SubjectOptionService;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class ManagementPaymentController extends AppController
{
    public function __construct(ReserveBaseService $reserveBaseService, SubjectAirplaneService $subjectAirplaneService, SubjectHotelService $subjectHotelService, SubjectOptionService $subjectOptionService, SupplierService $supplierService)
    {
        $this->reserveBaseService = $reserveBaseService;
        $this->subjectAirplaneService = $subjectAirplaneService;
        $this->subjectHotelService = $subjectHotelService;
        $this->subjectOptionService = $subjectOptionService;
        $this->supplierService = $supplierService;
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
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $reserveHashId 予約ID(ハッシュ)
     * @param string $supplierHashId 支払先ID(ハッシュ)。任意
     */
    public function item(string $agencyAccount, string $reserveHashId, ?string $supplierHashId = null)
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

        $supplier = null;
        if (($supplierId = \Hashids::decode($supplierHashId)[0] ?? null)) {
            $supplier = $this->supplierService->find($supplierId);
        }

        return view('staff.management_payment.item', compact("reserveHashId", "reserve", "supplierHashId", "supplier"));
    }

    /**
     * 商品詳細一覧
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $reserveHashId 予約ID(ハッシュ)
     * @param string $supplierHashId 支払先ID(ハッシュ)
     * @param string $subject 科目(オプション、航空券、ホテル)
     * @param string $itemHashId 商品ID(ハッシュ)
     */
    public function detail(string $agencyAccount, string $reserveHashId, string $supplierHashId, string $subject, string $itemHashId)
    {
        // 認可チェック
        $response = \Gate::inspect('viewAny', [new AccountPayableDetail]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 商品コードを取得
        $itemId = \Hashids::decode($itemHashId)[0] ?? null;
        $itemCode = null;
        if ($subject === config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) {
            $result = $this->subjectOptionService->find($itemId, ['code']);
            $itemCode = $result->code;
        } elseif ($subject === config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) {
            $result = $this->subjectAirplaneService->find($itemId, ['code']);
            $itemCode = $result->code;
        } elseif ($subject === config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) {
            $result = $this->subjectHotelService->find($itemId, ['code']);
            $itemCode = $result->code;
        }

        return view('staff.management_payment.detail', compact('reserveHashId', 'supplierHashId', 'subject', 'itemHashId', 'itemCode'));
    }

    // // 商品詳細
    // public function index()
    // {
    //     // 認可チェック
    //     $response = \Gate::inspect('viewAny', [new AccountPayableDetail]);
    //     if (!$response->allowed()) {
    //         abort(403);
    //     }

    //     return view('staff.management_payment.index');
    // }
}
