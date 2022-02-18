<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\VDirection\SearchResource;
use App\Services\VDirectionService;
use Illuminate\Http\Request;


class VDirectionController extends Controller
{
    public function __construct(VDirectionService $vDirectionService)
    {
        $this->vDirectionService = $vDirectionService;
    }

    /**
     * 方面検索
     */
    public function search(Request $request, $agencyAccount)
    {
        return SearchResource::collection(
            $this->vDirectionService->search(
                $agencyAccount,
                $request->v_direction,
                [],
                ['uuid','code','name'],
                50
            )
        );
    }
}
