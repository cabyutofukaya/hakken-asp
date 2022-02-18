<?php
namespace App\Http\ViewComposers\Staff\Web\Modelcourse;

use App\Models\WebModelcourse;
use App\Services\MasterAreaService;
use App\Services\StaffService;
use App\Services\VAreaService;
use App\Services\WebModelcourseService;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 詳細フォームに使う選択項目などを提供するViewComposer
 */
class ShowFormComposer
{
    use JsConstsTrait;

    public function __construct(
        MasterAreaService $masterAreaService,
        StaffService $staffService,
        VAreaService $vAreaService,
        WebModelcourseService $webModelcourseService
    ) {
        $this->masterAreaService = $masterAreaService;
        $this->staffService = $staffService;
        $this->vAreaService = $vAreaService;
        $this->webModelcourseService = $webModelcourseService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $webModelcourse = Arr::get($data, 'webModelcourse');

        //////////////////////////////////

        $my = auth("staff")->user();
        $agency = $my->agency;

        $id = $webModelcourse->id;

        $formSelects = [
            'stays' => get_const_item('web_modelcourses', 'stay'), // 泊数
            'previewUrl' => get_modelcourse_previewurl($agency->account, $webModelcourse->course_no)
        ];

        $consts = [
            'thumbSBaseUrl' => \Storage::disk('s3')->url(config('consts.const.UPLOAD_THUMB_S_DIR')),
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agency->account);

        $view->with(compact('id', 'formSelects', 'consts', 'jsVars'));
    }
}
