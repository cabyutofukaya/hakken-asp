<?php
namespace App\Http\ViewComposers\Staff\Area;

use Illuminate\Support\Arr;
use Illuminate\View\View;
use App\Services\AreaService;
use App\Services\VDirectionService;
use App\Traits\JsConstsTrait;

/**
 * 作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    use JsConstsTrait;

    public function __construct(AreaService $areaService, VDirectionService $vDirectionService)
    {
        $this->areaService = $areaService;
        $this->vDirectionService = $vDirectionService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $agencyAccount = request()->agencyAccount;

        $defaultValue = session()->getOldInput();

        if (Arr::get($defaultValue, "v_direction_uuid")) { // 方面IDがある場合は初期値用に名称等も取得
            $defaultValue['v_direction'] = $this->vDirectionService->getDefaultSelectRow($defaultValue['v_direction_uuid']);
        }

        $formSelects = [];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('defaultValue', 'formSelects', 'jsVars'));
    }
}
