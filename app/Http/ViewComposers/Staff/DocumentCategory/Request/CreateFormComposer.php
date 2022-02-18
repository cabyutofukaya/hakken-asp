<?php
namespace App\Http\ViewComposers\Staff\DocumentCategory\Request;

use App\Traits\DocumentTrait;
use App\Services\DocumentCommonService;
use App\Services\DocumentRequestService;
use Illuminate\View\View;

/**
 * 請求書設定作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    use DocumentTrait;

    public function __construct(DocumentCommonService $documentCommonService, DocumentRequestService $documentRequestService)
    {
        $this->documentCommonService = $documentCommonService;
        $this->documentRequestService = $documentRequestService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $defaultValue = session()->getOldInput();
        // 選択系メニューの初期化
        if (!isset($defaultValue['setting'])) {
            // 全チェックで初期化
            $defaultValue['setting'][config('consts.document_requests.DISPLAY_BLOCK')] = $this->settingFlatAll(config('consts.document_requests.DISPLAY_BLOCK_LIST'));

            $defaultValue['setting'][config('consts.document_requests.RESERVATION_INFO')] = $this->settingFlatAll(config('consts.document_requests.RESERVATION_INFO_LIST'));

            $defaultValue['setting'][config('consts.document_requests.AIR_TICKET_INFO')]
            = $this->settingFlatAll(config('consts.document_requests.AIR_TICKET_INFO_LIST'));

            $defaultValue['setting'][config('consts.document_requests.BREAKDOWN_PRICE')]
            = $this->settingFlatAll(config('consts.document_requests.BREAKDOWN_PRICE_LIST'));
        }
        if (!isset($defaultValue['seal'])) {
            $defaultValue['seal'] = 0;
        }
        if (!isset($defaultValue['document_common_id'])) {
            $defaultValue['document_common_id'] = $this->documentCommonService->getDefaultId(auth('staff')->user()->agency_id);
        }

        // form系項目
        $formSelects = [
            'sealNumbers' => $this->documentRequestService->getSealRange(),
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelect(auth("staff")->user()->agency_id),
        ];
        $view->with(compact('defaultValue', 'formSelects'));
    }
}
