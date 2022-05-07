<?php

namespace App\Http\Controllers\Staff;

use App\Models\AccountPayableDetail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManagementPaymentController extends AppController
{
    /**
     * 予約毎一覧
     */
    public function reserve()
    {
        // 認可チェック
        $response = \Gate::inspect('viewAny', [new AccountPayableDetail]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.management_payment.reserve');
    }

    // 一覧
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
