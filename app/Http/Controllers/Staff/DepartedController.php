<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Reserve;
use App\Services\DepartedService;
use Illuminate\Http\Request;

/**
 * 催行済み管理
 */
class DepartedController extends Controller
{
    public function __construct(DepartedService $departedService)
    {
        $this->departedService = $departedService;
    }

    /**
     * 催行済み一覧
     */
    public function index()
    {
        // 認可チェック
        $response = \Gate::inspect('viewAny', [new Reserve]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        return view('staff.departed.index');
    }

    /**
     * 詳細表示ページ
     *
     * @param string $controlNumber 予約番号
     */
    public function show(string $agencyAccount, string $controlNumber)
    {
        // departedServiceを介するため$reserveは催行済みレコード
        $reserve = $this->departedService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = \Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 申し込み種別に合わせてviewを変える
        if ($reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_ASP')) { // ASP受付
            return view('staff.reserve.show', compact('reserve'));
        } elseif ($reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) { // WEB受付
            return view('staff.web.reserve.show', compact('reserve'));
        }
        abort(404);
    }
}
