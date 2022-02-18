<?php
namespace App\Http\ViewComposers\Staff\DocumentCategory\Receipt;

use Illuminate\Support\Arr;
use Illuminate\View\View;
use App\Services\DocumentCommonService;

/**
 * 領収書更新フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
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
        $data = $view->getData(); // controllerにセットされたデータを取得
        $documentReceipt = Arr::get($data, 'documentReceipt');

        //////////////////////////////////

        // POST値を優先して、入力値をセット
        $defaultValue = session()->getOldInput();
        foreach ($documentReceipt->toArray() as $key => $val) {
            if (!isset($defaultValue[$key])) {
                $defaultValue[$key] = $val;
            }
        }

        $formSelects = [
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelect(auth("staff")->user()->agency_id),
        ];
        $view->with(compact('defaultValue', 'formSelects'));
    }
}
