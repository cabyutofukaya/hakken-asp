<?php
namespace App\Http\ViewComposers\Staff\City;

use App\Services\CityService;
use App\Services\MasterAreaService;
use App\Services\VAreaService;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    use JsConstsTrait;

    public function __construct(CityService $cityService, VAreaService $vAreaService, MasterAreaService $masterAreaService)
    {
        $this->cityService = $cityService;
        $this->vAreaService = $vAreaService;
        $this->masterAreaService = $masterAreaService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $agencyAccount = request()->agencyAccount;

        $defaultValue = session()->getOldInput();

        if (Arr::get($defaultValue, "v_area_uuid")) { // 国・地域IDがある場合は初期値用に名称等も取得
            $defaultValue['v_area'] = $this->vAreaService->getDefaultSelectRow($defaultValue['v_area_uuid']);
        }

        $formSelects = [
            'defaultAreas' => $this->masterAreaService->getDefaultOptions(['label' => '---', 'value' => '']),
            // 'vAreas' => ['' => 'すべて'] + $this->vAreaService->getNameListByAgencyAccount($agencyAccount), // 国・地域リスト
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('defaultValue', 'formSelects', 'jsVars'));
    }
}
