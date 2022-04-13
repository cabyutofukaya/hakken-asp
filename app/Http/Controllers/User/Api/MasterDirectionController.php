<?php

namespace App\Http\Controllers\User\Api;

use Validator;
use Auth;
use App\Services\MasterDirectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MasterDirectionController extends BaseController
{
    public function __construct(MasterDirectionService $masterDirectionService)
    {
        // $this->guard = "api";
        $this->masterDirectionService = $masterDirectionService;
    }

    public function list(string $version)
    {
        // 地域情報
        return $this->masterDirectionService->getWebDirections();
    }
}
