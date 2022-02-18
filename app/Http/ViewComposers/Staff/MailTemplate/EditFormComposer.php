<?php
namespace App\Http\ViewComposers\Staff\MailTemplate;

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
        $mailTemplate = Arr::get($data, 'mailTemplate');

        //////////////////////////////////

        // 基本項目
        $defaultValue = [
            'name' => old('name', $mailTemplate->name),
            'description' => old('description', $mailTemplate->description),
            'subject' => old('subject', $mailTemplate->subject),
            'body' => old('body', $mailTemplate->body),
            'setting' => old('setting', $mailTemplate->setting),
        ];

        $view->with(compact('defaultValue'));
    }
}
