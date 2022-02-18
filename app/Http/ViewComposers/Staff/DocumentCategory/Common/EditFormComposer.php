<?php
namespace App\Http\ViewComposers\Staff\DocumentCategory\Common;

use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 共通設定作成フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $documentCommon = Arr::get($data, 'documentCommon');

        //////////////////////////////////

        // POST値を優先して、入力値をセット
        $defaultValue = session()->getOldInput();
        foreach ($documentCommon->toArray() as $key => $val) {
            if (!isset($defaultValue[$key])) {
                $defaultValue[$key] = $val;
            }
        }
        
        $view->with(compact('defaultValue'));
    }
}
