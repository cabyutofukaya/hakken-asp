<?php
namespace App\Http\ViewComposers\Staff\Web\Company;

use App\Services\WebCompanyService;
use Illuminate\View\View;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;

/**
 * 編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use JsConstsTrait;

    public function __construct(
    ) {
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $webCompany = Arr::get($data, 'webCompany');

        //////////////////////////////////

        $my = auth("staff")->user();
        $agency = $my->agency;

        //////////////// form初期値を設定 ////////////////

        // 基本項目
        foreach (['explanation','logo_image','images'] as $f) {
            $defaultValue[$f] = old($f, data_get($webCompany, $f));
        }

        $consts = [
            'thumbSBaseUrl' => \Storage::disk('s3')->url(config('consts.const.UPLOAD_THUMB_S_DIR')),
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agency->account);

        $view->with(compact('webCompany','agency','defaultValue','consts','jsVars'));
    }
}
