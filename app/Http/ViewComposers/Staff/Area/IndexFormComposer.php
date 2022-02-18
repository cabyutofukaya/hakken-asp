<?php
namespace App\Http\ViewComposers\Staff\Area;

use App\Services\AreaService;
use App\Services\AgencyRoleService;
use App\Services\VDirectionService;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Request;

/**
 *一覧ページに使う選択項目などを提供するViewComposer
 */
class IndexFormComposer
{
    public function __construct(AreaService $areaService, AgencyRoleService $agencyRoleService, VDirectionService $vDirectionService)
    {
        $this->areaService = $areaService;
        $this->agencyRoleService = $agencyRoleService;
        $this->vDirectionService = $vDirectionService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $agencyAccount = request()->agencyAccount;

        $searchParam = [];// 検索パラメータ
        foreach (['code', 'v_direction_uuid', 'name', 'name_en', 'search_option_open'] as $p) {
            $searchParam[$p] = Request::get($p);
        }

        $formSelects = [
            'vDirections' => ['' => 'すべて'] + $this->vDirectionService->getNameListByAgencyAccount($agencyAccount), // 方向
        ];

        $view->with(compact('searchParam', 'formSelects'));
    }
}
