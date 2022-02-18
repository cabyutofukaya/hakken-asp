<?php
namespace App\Http\ViewComposers\Staff\Direction;

use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * メールテンプレート編集フォームに使う選択項目などを提供するViewComposer
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
        $vDirection = Arr::get($data, 'vDirection');

        //////////////////////////////////

        // 基本項目
        $defaultValue = [
            'code' => old('code', $vDirection->code),
            'name' => old('name', $vDirection->name),
        ];

        $view->with(compact('defaultValue'));
    }
}
