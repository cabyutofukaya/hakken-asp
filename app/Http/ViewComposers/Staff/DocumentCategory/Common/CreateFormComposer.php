<?php
namespace App\Http\ViewComposers\Staff\DocumentCategory\Common;

use Illuminate\View\View;
use App\Traits\DocumentTrait;

/**
 * 共通設定作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    use DocumentTrait;
    
    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $defaultValue = session()->getOldInput();
        if (!isset($defaultValue['setting'])) { // POST値がない場合はsetting値を全チェックで初期化
            $defaultValue['setting'][config('consts.document_commons.ADDRESS_PERSON')] = $this->settingFlatAll(config('consts.document_commons.ADDRESS_PERSON_LIST'));
            
            $defaultValue['setting'][config('consts.document_commons.ADDRESS_BUSINESS')] = $this->settingFlatAll(config('consts.document_commons.ADDRESS_BUSINESS_LIST'));

            $defaultValue['setting'][config('consts.document_commons.COMPANY_INFO')]
            = $this->settingFlatAll(config('consts.document_commons.COMPANY_INFO_LIST'));
        }

        $view->with(compact('defaultValue'));
    }
}
