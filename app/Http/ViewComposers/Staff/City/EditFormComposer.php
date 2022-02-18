<?php
namespace App\Http\ViewComposers\Staff\City;

use App\Services\MasterAreaService;
use App\Services\VAreaService;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use JsConstsTrait;

    public function __construct(VAreaService $vAreaService, MasterAreaService $masterAreaService)
    {
        $this->vAreaService = $vAreaService;
        $this->masterAreaService = $masterAreaService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $city = Arr::get($data, 'city');

        //////////////////////////////////

        $agencyAccount = request()->agencyAccount;

        // 基本項目
        $defaultValue = [
            'code' => old('code', $city->code),
            'v_area_uuid' => old('v_area_uuid', $city->v_area_uuid),
            'name' => old('name', $city->name),
        ];

        if (Arr::get($defaultValue, "v_area_uuid")) { // 国・地域IDがある場合は初期値用に名称等も取得
            $defaultValue['v_area'] = $this->vAreaService->getDefaultSelectRow($defaultValue['v_area_uuid']);
        }

        $formSelects = [
            'defaultAreas' => $this->masterAreaService->getDefaultOptions(['label' => '---', 'value' => '']),
            // 'vAreas' => ['' => 'すべて'] + $this->vAreaService->getNameListByAgencyAccount($agencyAccount), // 国・地域
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('formSelects','defaultValue','jsVars'));
    }
}
