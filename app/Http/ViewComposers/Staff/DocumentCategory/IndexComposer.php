<?php
namespace App\Http\ViewComposers\Staff\DocumentCategory;

use App\Services\DocumentCategoryService;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Lang;

/**
 * 帳票設定一覧ページの項目などを提供するViewComposer
 */
class IndexComposer
{
    public function __construct(DocumentCategoryService $documentCategoryService)
    {
        $this->documentCategoryService = $documentCategoryService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $documentCategories = $this->documentCategoryService->all();

        $currentTab = request()->get("tab", config('consts.document_categories.DOCUMENT_CATEGORY_COMMON')); // 選択タブ

        $consts = [
            // 領収書タブ値(create可能かどうかの判定に使用)
            'receipt' => config('consts.document_categories.DOCUMENT_CATEGORY_RECEIPT')
        ];

        $permission = [
            'common' => [
                'create' => \Auth::user('staff')->can('create', new \App\Models\DocumentCommon), // 作成権限
            ],
            'quote' => [
                'create' => \Auth::user('staff')->can('create', new \App\Models\DocumentQuote), // 作成権限
            ],
            'request' => [
                'create' => \Auth::user('staff')->can('create', new \App\Models\DocumentRequest), // 作成権限
            ],
            'request' => [
                'create' => \Auth::user('staff')->can('create', new \App\Models\DocumentRequest), // 作成権限
            ],
            'request_all' => [
                'create' => \Auth::user('staff')->can('create', new \App\Models\DocumentRequestAll), // 作成権限
            ],
            'receipt' => [
                'create' => \Auth::user('staff')->can('create', new \App\Models\DocumentReceipt), // 作成権限
            ],
        ];

        $view->with(compact('documentCategories','currentTab', 'consts', 'permission'));
    }
}
