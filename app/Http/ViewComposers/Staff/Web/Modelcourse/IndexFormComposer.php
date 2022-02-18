<?php
namespace App\Http\ViewComposers\Staff\Web\Modelcourse;

use App\Models\WebModelcourse;
use App\Services\WebModelcourseService;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * indexフォームに使う選択項目などを提供するViewComposer
 */
class IndexFormComposer
{
    use JsConstsTrait;

    public function __construct(
        WebModelcourseService $webModelcourseService
    ) {
        $this->webModelcourseService = $webModelcourseService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得

        //////////////////////////////////

        $my = auth("staff")->user();
        $agency = $my->agency;

        //////////////// form初期値を設定 ////////////////

        $myId = $my->id; // 自身のスタッフID

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agency->account);

        $view->with(compact('myId','jsVars'));
    }
}
