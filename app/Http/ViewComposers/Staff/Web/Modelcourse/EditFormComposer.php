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
 * 新規・編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
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
        $webModelcourse = $webModelcourse ? $webModelcourse : new WebModelcourse;

        //////////////////////////////////

        $my = auth("staff")->user();
        $agency = $my->agency;

        //////////////// form初期値を設定 ////////////////

        // 基本項目
        foreach (['name','description','stays','price_per_ad','price_per_ch','price_per_inf','departure_id','departure_place','destination_id','destination_place','author_id'] as $f) {
            if ($f === 'stays') { // 日数は初期値をセット
                $defaultValue[$f] = old($f, data_get($webModelcourse, $f, config('consts.web_modelcourses.DEFAULT_STAY')));
            } elseif ($f === 'author_id') { // 作成者がセットされていない場合はログイン中のユーザーで初期化
                if ($webModelcourse->id) { // 編集
                    $defaultValue[$f] = old($f, data_get($webModelcourse, $f));
                } else { // 新規
                    $defaultValue[$f] = old($f, data_get($webModelcourse, $f, $my->id));
                }
            } else {
                $defaultValue[$f] = old($f, data_get($webModelcourse, $f));
            }
        }

        // 出発地・目的地
        if (Arr::get($defaultValue, "departure_id")) { // 出発地IDがある場合は初期値用に名称等も取得
            $defaultValue['departure'] = $this->vAreaService->getDefaultSelectRow($defaultValue['departure_id']);
        }
        if (Arr::get($defaultValue, "destination_id")) { // 目的地IDがある場合は初期値用に名称等も取得
            $defaultValue['destination'] = $this->vAreaService->getDefaultSelectRow($defaultValue['destination_id']);
        }

        // tag
        if (!old('web_modelcourse_tags.tag')) {
            $defaultValue['web_modelcourse_tags']['tag'] = $webModelcourse->web_modelcourse_tags->pluck("tag")->toArray();
        } else {
            $defaultValue['web_modelcourse_tags']['tag'] = old('web_modelcourse_tags.tag');
        }

        /**
         * 画像
         */
        if (!old('web_modelcourse_photo')) {
            $defaultValue['web_modelcourse_photo'] = $webModelcourse->web_modelcourse_photo->id ? $webModelcourse->web_modelcourse_photo->toArray() : null;
        } else {
            $defaultValue['web_modelcourse_photo'] = old('web_modelcourse_photo');
        }

        $formSelects = [
            'staffs' => ['' => '---'] + $this->staffService->getIdNameSelectSafeValues($agency->id, [$webModelcourse->author_id]), // 自社スタッフ
            'stays' => get_const_item('web_modelcourses', 'stay'), // 泊数
            'defaultAreas' => $this->masterAreaService->getDefaultOptions(['label' => '---', 'value' => '']),
        ];

        $consts = [
            'thumbSBaseUrl' => \Storage::disk('s3')->url(config('consts.const.UPLOAD_THUMB_S_DIR')),
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agency->account);

        $view->with(compact('defaultValue', 'formSelects', 'consts', 'jsVars'));
    }
}
