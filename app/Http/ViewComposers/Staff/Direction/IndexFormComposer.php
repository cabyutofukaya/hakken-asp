<?php
namespace App\Http\ViewComposers\Staff\Direction;

use App\Services\DirectionService;
use App\Services\AgencyRoleService;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Lang;
use Request;

/**
 * 一覧ページに使う選択項目などを提供するViewComposer
 */
class IndexFormComposer
{
    public function __construct(DirectionService $directionService, AgencyRoleService $agencyRoleService)
    {
        $this->directionService = $directionService;
        $this->agencyRoleService = $agencyRoleService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $searchParam = [];// 検索パラメータ
        foreach (['code', 'name', 'search_option_open'] as $p) {
            $searchParam[$p] = Request::get($p);
        }

        $view->with(compact('searchParam'));
    }
}
