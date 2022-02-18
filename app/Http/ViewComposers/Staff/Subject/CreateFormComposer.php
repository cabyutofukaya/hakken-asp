<?php
namespace App\Http\ViewComposers\Staff\Subject;

use Illuminate\View\View;
use App\Services\SubjectCategoryService;
use Illuminate\Support\Arr;

/**
 * 作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    
    public function __construct(SubjectCategoryService $subjectCategoryService)
    {
        $this->subjectCategoryService = $subjectCategoryService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $category = old("category", config('consts.subject_categories.DEFAULT_SUBJECT_CATEGORY')); // 初期画面でもデフォルト値が設定できるように明示的に初期化

        $formSelects = [
            'subjectCategories' => $this->subjectCategoryService->all()->pluck('name', 'code')->toArray(),
        ];
        
        $view->with(compact('formSelects','category'));
    }
}
