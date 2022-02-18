<?php

namespace App\Http\Controllers\Staff\Api;

use Gate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agency;
use App\Models\User;
use App\Services\UserService;
use App\Http\Requests\Admin\AgencyIsAccountExistsRequest;
use Log;

class IndividualController extends Controller
{
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * 個人顧客一覧を取得
     */
    public function index()
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new User);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return $this->userService->paginateByAgencyId((int)auth('staff')->user()->agency->id, 30, []);

    }
}
