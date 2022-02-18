<?php
namespace App\Http\ViewComposers\Staff\City;

use App\Services\CityService;
use App\Services\AgencyRoleService;
use App\Services\VAreaService;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Request;

/**
 *一覧ページに使う選択項目などを提供するViewComposer
 */
class IndexFormComposer
{
    public function __construct(CityService $cityService, AgencyRoleService $agencyRoleService, VAreaService $vAreaService)
    {
        $this->cityService = $cityService;
        $this->agencyRoleService = $agencyRoleService;
        $this->vAreaService = $vAreaService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $agencyAccount = request()->agencyAccount;

        $searchParam = [];// 検索パラメータ
        foreach (['code', 'v_area_uuid', 'name', 'search_option_open'] as $p) {
            $searchParam[$p] = Request::get($p);
        }

        $formSelects = [
            'vAreas' => ['' => 'すべて'] + $this->vAreaService->getNameListByAgencyAccount($agencyAccount), // 方向ステータス
        ];

        $view->with(compact('searchParam', 'formSelects'));
    }
}
