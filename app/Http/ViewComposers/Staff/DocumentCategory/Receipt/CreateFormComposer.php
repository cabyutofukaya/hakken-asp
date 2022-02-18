<?php
namespace App\Http\ViewComposers\Staff\DocumentCategory\Receipt;

use Illuminate\View\View;
use App\Services\DocumentCommonService;

/**
 * 領収書作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    public function __construct(DocumentCommonService $documentCommonService)
    {
        $this->documentCommonService = $documentCommonService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {        
        $defaultValue = session()->getOldInput();
        if(!isset($defaultValue['document_common_id'])){
            $defaultValue['document_common_id'] = $this->documentCommonService->getDefaultId(auth('staff')->user()->agency_id);
        }

        $formSelects = [
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelect(auth("staff")->user()->agency_id),
        ];
        $view->with(compact('defaultValue', 'formSelects'));
    }
}
