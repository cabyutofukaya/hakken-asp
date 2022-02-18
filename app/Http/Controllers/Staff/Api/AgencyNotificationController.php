<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Services\AgencyNotificationService;
use App\Http\Requests\Staff\AgencyNotificationReadRequest;
use App\Http\Resources\Staff\AgencyNotification\IndexResource;
use Gate;
use Log;
use Illuminate\Http\Request;

class AgencyNotificationController extends Controller
{
    public function __construct(AgencyNotificationService $agencyNotificationService)
    {
        $this->agencyNotificationService = $agencyNotificationService;
    }

    /**
     * 一覧取得
     */
    public function index($agencyAccount)
    {
        $limit = request()->get("per_page", 15);

        return IndexResource::collection($this->agencyNotificationService->paginateByAgencyId(
            auth('staff')->user()->agency_id,
            $limit,
        ));
    }

    /**
     * 既読処理
     */
    public function read(AgencyNotificationReadRequest $request)
    {
        $ids = $request->input('ids');
        if ($this->agencyNotificationService->read($ids)) {
            return $ids;
        }
    }
}
