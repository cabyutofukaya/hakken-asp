<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\BankKinyuRequest;
use App\Http\Requests\Staff\BankTenpoRequest;
use App\Http\Resources\Staff\Bank\KinyuResource;
use App\Http\Resources\Staff\Bank\TenpoResource;
use App\Models\Bank;
use App\Services\BankService;
use Gate;
use Log;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function __construct(BankService $bankService)
    {
        $this->bankService = $bankService;
    }

    /**
     * 支店情報を取得
     *
     * @param string $agencyAccount
     */
    public function findTenpoName(BankTenpoRequest $request, $agencyAccount)
    {
        $kinyuCode = $request->get('kinyu_code'); // 金融期間コード
        $tenpoCode = $request->get('tenpo_code'); // 店舗コード

        $results = $this->bankService->getTenpoNamesForSelectItem($kinyuCode, $tenpoCode);

        return [
            'data' => $results
        ];
    }
}
