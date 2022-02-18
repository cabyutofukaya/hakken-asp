<?php
namespace App\Http\ViewComposers\Staff\Subject;

use App\Services\SubjectCategoryService;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use JsConstsTrait;

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
        $formSelects = [
            'subjectCategories' => $this->subjectCategoryService->all()->pluck('name', 'code')->toArray(),
        ];

        $view->with(compact('formSelects', 'jsVars'));
    }
}
