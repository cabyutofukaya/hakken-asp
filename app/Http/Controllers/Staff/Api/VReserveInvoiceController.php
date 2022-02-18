<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\VReserveInvoice;
use App\Http\Controllers\Controller;
use App\Services\VReserveInvoiceService;
use Illuminate\Http\Request;
use App\Http\Resources\Staff\VReserveInvoice\IndexResource;

class VReserveInvoiceController extends Controller
{
    public function __construct(VReserveInvoiceService $vReserveInvoiceService)
    {
        $this->vReserveInvoiceService = $vReserveInvoiceService;
    }

    /**
     * 請求管理一覧
     */
    public function index(string $agencyAccount)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new VReserveInvoice);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['status','reserve_number','applicant_name','last_manager_id','issue_date_from','issue_date_to','payment_deadline_from','payment_deadline_to']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出

                if (in_array($key, ['issue_date_from','issue_date_to','payment_deadline_from','payment_deadline_to'], true) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }
            }
        }

        return IndexResource::collection(
            $this->vReserveInvoiceService->paginateByAgencyAccount(
                $agencyAccount,
                $params,
                request()->get("per_page", 10),
                [
                    'reserve',
                    'agency_bundle_deposits.v_agency_bundle_deposit_custom_values',
                    'agency_deposits.v_agency_deposit_custom_values',
                ]
            )
        );
    }
}
