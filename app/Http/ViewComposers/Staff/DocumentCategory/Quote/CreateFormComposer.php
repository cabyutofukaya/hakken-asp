<?php
namespace App\Http\ViewComposers\Staff\DocumentCategory\Quote;

use App\Traits\DocumentTrait;
use App\Services\DocumentCommonService;
use App\Services\DocumentQuoteService;
use Illuminate\View\View;

/**
 * 共通設定作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    use DocumentTrait;

    public function __construct(DocumentCommonService $documentCommonService, DocumentQuoteService $documentQuoteService)
    {
        $this->documentCommonService = $documentCommonService;
        $this->documentQuoteService = $documentQuoteService;
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
            $defaultValue['setting'][config('consts.document_quotes.DISPLAY_BLOCK')] = $this->settingFlatAll(config('consts.document_quotes.DISPLAY_BLOCK_LIST'));

            $defaultValue['setting'][config('consts.document_quotes.RESERVATION_INFO')] = $this->settingFlatAll(config('consts.document_quotes.RESERVATION_INFO_LIST'));

            $defaultValue['setting'][config('consts.document_quotes.AIR_TICKET_INFO')]
            = $this->settingFlatAll(config('consts.document_quotes.AIR_TICKET_INFO_LIST'));

            $defaultValue['setting'][config('consts.document_quotes.BREAKDOWN_PRICE')]
            = $this->settingFlatAll(config('consts.document_quotes.BREAKDOWN_PRICE_LIST'));
        }
        if (!isset($defaultValue['seal'])) {
            $defaultValue['seal'] = 0;
        }
        if (!isset($defaultValue['document_common_id'])) {
            $defaultValue['document_common_id'] = $this->documentCommonService->getDefaultId(auth('staff')->user()->agency_id);
        }

        // form系項目
        $formSelects = [
            'sealNumbers' => $this->documentQuoteService->getSealRange(),
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelect(auth("staff")->user()->agency_id),
        ];
        $view->with(compact('defaultValue', 'formSelects'));
    }
}
