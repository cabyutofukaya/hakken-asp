<?php
namespace App\Http\ViewComposers\Staff\MailTemplate;

use Illuminate\Support\Arr;
use Illuminate\View\View;
use Lang;
use App\Services\MailTemplateService;

/**
 * メールテンプレート作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    public function __construct(MailTemplateService $mailTemplateService)
    {
        $this->mailTemplateService = $mailTemplateService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {        
        $defaultValue = session()->getOldInput();

        $view->with(compact('defaultValue'));
    }
}
