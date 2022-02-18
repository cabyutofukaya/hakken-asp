<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ModelLogService;

class ModelLogController extends Controller
{
    const LIST_PER_PAGE = 20; // 1ページ表示件数

    public function __construct(ModelLogService $modelLogService)
    {
        $this->modelLogService = $modelLogService;
    }

    public function index(Request $request)
    {
        $conditions = [
            'model' => $request->get("model"), // モデル名
            'model_id' => $request->get("model_id") // モデルID
        ];

        return view('admin.model_log.index', [
            'modelLogs' => $this->modelLogService->paginate(self::LIST_PER_PAGE, $conditions, 'AND')
        ]);
    }
}
