<?php
namespace App\Http\ViewComposers\Staff\Common;

use Request;
use Illuminate\View\View;
use App\Traits\JsConstsTrait;

/**
 * ニュース
 */
class NewsComposer
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
        // reactに渡す各種定数
        $jsVars = $this->getJsVars(request()->agencyAccount);

        $view->with(compact('jsVars'));
    }
}
