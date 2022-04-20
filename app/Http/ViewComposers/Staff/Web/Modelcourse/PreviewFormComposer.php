<?php
namespace App\Http\ViewComposers\Staff\Web\Modelcourse;

use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * プレビューに使う選択項目などを提供するViewComposer
 */
class PreviewFormComposer
{
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

        $formSelects = [
            'stays' => get_const_item('web_modelcourses', 'stay'), // 泊数
            'previewUrl' => get_modelcourse_previewurl($agency->account, $webModelcourse->course_no)
        ];

        $consts = [
            'thumbBaseUrl' => \Storage::disk('s3')->url(config('consts.const.UPLOAD_IMAGE_DIR')),
            'thumbSBaseUrl' => \Storage::disk('s3')->url(config('consts.const.UPLOAD_THUMB_S_DIR')),
            'thumbMBaseUrl' => \Storage::disk('s3')->url(config('consts.const.UPLOAD_THUMB_M_DIR')),
        ];

        $view->with(compact('formSelects', 'consts'));
    }
}
