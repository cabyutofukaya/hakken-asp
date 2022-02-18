<?php
namespace App\Http\ViewComposers\Staff\Area;

use App\Services\VDirectionService;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use JsConstsTrait;

    public function __construct(VDirectionService $vDirectionService)
    {
        $this->vDirectionService = $vDirectionService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $vArea = Arr::get($data, 'vArea');

        //////////////////////////////////

        $agencyAccount = request()->agencyAccount;

        // 基本項目
        $defaultValue = [
            'code' => old('code', $vArea->code),
            'v_direction_uuid' => old('v_direction_uuid', $vArea->v_direction_uuid),
            'name' => old('name', $vArea->name),
            'name_en' => old('name_en', $vArea->name_en),
        ];

        if (Arr::get($defaultValue, "v_direction_uuid")) { // 方面IDがある場合は初期値用に名称等も取得
            $defaultValue['v_direction'] = $this->vDirectionService->getDefaultSelectRow($defaultValue['v_direction_uuid']);
        }

        $formSelects = [
            'vDirections' => ['' => 'すべて'] + $this->vDirectionService->getNameListByAgencyAccount($agencyAccount), // 方向
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);
        
        $view->with(compact('formSelects', 'defaultValue', 'jsVars'));
    }
}
