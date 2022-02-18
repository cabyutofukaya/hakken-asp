<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ActLogService;

class ActLogController extends Controller
{
    const LIST_PER_PAGE = 20; // 1ページ表示件数

    public function __construct(ActLogService $actLogService)
    {
        $this->actLogService = $actLogService;
    }

    public function index(Request $request)
    {
        $conditions = [];

        return view('admin.act_log.index', [
            'actLogs' => $this->actLogService->paginate(self::LIST_PER_PAGE, $conditions, 'AND')
        ]);
    }
}
