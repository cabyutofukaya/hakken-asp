<?php
namespace App\Http\ViewComposers\Staff\DocumentCategory\Quote;

use App\Services\DocumentCommonService;
use App\Services\DocumentQuoteService;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 共通設定作成フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    public function __construct(DocumentQuoteService $documentQuoteService, DocumentCommonService $documentCommonService)
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
        $data = $view->getData(); // controllerにセットされたデータを取得
        $documentQuote = Arr::get($data, 'documentQuote');

        //////////////////////////////////

        // POST値を優先して、入力値をセット
        $defaultValue = session()->getOldInput();
        foreach ($documentQuote->toArray() as $key => $val) {
            if (!isset($defaultValue[$key])) {
                $defaultValue[$key] = $val;
            }
        }

        $formSelects = [
            'sealNumbers' => $this->documentQuoteService->getSealRange(),
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelect(auth("staff")->user()->agency_id),
        ];

        $view->with(compact('defaultValue', 'formSelects'));
    }
}
