<?php
namespace App\Http\ViewComposers\Staff\Web\ReserveEstimate;

use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Traits\JsConstsTrait;
use Illuminate\View\View;
use Illuminate\Support\Arr;
use Request;

/**
 * リクエスト詳細ページに使う選択項目などを提供するViewComposer
 */
class EstimateRequestFormComposer
{
    use JsConstsTrait;

    public function __construct(
        UserCustomCategoryService $userCustomCategoryService,
        UserCustomItemService $userCustomItemService
    ) {
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->userCustomItemService = $userCustomItemService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserve = Arr::get($data, 'reserve');

        //////////////////////////////////

        $agencyAccount = request()->agencyAccount;

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);
        
        $view->with(compact('agencyAccount', 'jsVars'));
    }
}
