<?php
namespace App\Http\ViewComposers\Staff\DocumentCategory\Request;

use App\Services\DocumentCommonService;
use App\Services\DocumentRequestService;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 請求書設定作成フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    public function __construct(DocumentRequestService $documentRequestService, DocumentCommonService $documentCommonService)
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
        $data = $view->getData(); // controllerにセットされたデータを取得
        $documentRequest = Arr::get($data, 'documentRequest');

        //////////////////////////////////

        // POST値を優先して、入力値をセット
        $defaultValue = session()->getOldInput();
        foreach ($documentRequest->toArray() as $key => $val) {
            if (!isset($defaultValue[$key])) {
                $defaultValue[$key] = $val;
            }
        }

        $formSelects = [
            'sealNumbers' => $this->documentRequestService->getSealRange(),
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelect(auth("staff")->user()->agency_id),
        ];
        $view->with(compact('defaultValue', 'formSelects'));
    }
}
