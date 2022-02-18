<?php
namespace App\Http\ViewComposers\Staff\DocumentCategory\RequestAll;

use App\Services\DocumentCommonService;
use App\Services\DocumentRequestAllService;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 請求書設定作成フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    public function __construct(DocumentCommonService $documentCommonService, DocumentRequestAllService $documentRequestAllService)
    {
        $this->documentCommonService = $documentCommonService;
        $this->documentRequestAllService = $documentRequestAllService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $documentRequestAll = Arr::get($data, 'documentRequestAll');

        //////////////////////////////////

        // POST値を優先して、入力値をセット
        $defaultValue = session()->getOldInput();
        foreach ($documentRequestAll->toArray() as $key => $val) {
            if (!isset($defaultValue[$key])) {
                $defaultValue[$key] = $val;
            }
        }

        $formSelects = [
            'sealNumbers' => $this->documentRequestAllService->getSealRange(),
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelect(auth("staff")->user()->agency_id),
        ];
        $view->with(compact('defaultValue', 'formSelects'));
    }
}
