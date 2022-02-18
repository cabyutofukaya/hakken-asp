<?php

namespace App\Http\Controllers\Api\Batch;

use DB;
use App\Http\Controllers\Controller;
use App\Services\ContractService;
use Illuminate\Http\Request;


class ContractController extends Controller
{
    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    /**
     * 契約更新
     *
     * @return \Illuminate\Http\Response
     */
    public function renewal()
    {
        try {
            $transactions = DB::transaction(function () {
                return $this->contractService->renewal();
            });
            return response()->json();

        }catch(\Exception $e){
            \Log::error($e->getMessage());
        }
        abort(500);
    }

}
