<?php
namespace App\Http\ViewComposers\Staff\Subject\Hotel;

use App\Services\CityService;
use App\Services\SubjectCategoryService;
use App\Services\SupplierService;
use App\Services\UserCustomItemService;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use App\Traits\UserCustomItemTrait;
use App\Traits\SubjectTrait;

/**
 * ホテル科目作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    use UserCustomItemTrait,SubjectTrait;

    public function __construct(SubjectCategoryService $subjectCategoryService, CityService $cityService, SupplierService $supplierService, UserCustomItemService $userCustomItemService)
    {
        $this->subjectCategoryService = $subjectCategoryService;
        $this->cityService = $cityService;
        $this->supplierService = $supplierService;
        $this->userCustomItemService = $userCustomItemService;

    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $agencyAccount = request()->agencyAccount;

        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT');

        $defaultValue = session()->getOldInput();
        // POST元が当該カテゴリでない場合は料金関連項目をクリア
        if (Arr::get($defaultValue, 'category') !== config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) {
            $defaultValue = $this->initPriceField($defaultValue);
        }


        // カスタム項目を取得しつつ、カスタム項目のinput初期値をセット
        $userCustomItems = $this->getUserCustomItemsAndSetCustomFieldDefaultCreateInput(
            $defaultValue, 
            $this->userCustomItemService, 
            $agencyAccount, 
            config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT'), 
            [],
            [
                'display_position' => config('consts.user_custom_items.POSITION_SUBJECT_HOTEL')
            ]
        );

        
        $formSelects = [
            'zeiKbns' => get_const_item('subject_categories', 'zei_kbn'),
            // 'cities' => ['' => 'すべて'] + $this->cityService->getNameSelectByAgencyAccount($agencyAccount),
            'suppliers' => ['' => '-'] + $this->supplierService->getNameSelectByAgencyAccount($agencyAccount),
            'userCustomItems' => $userCustomItems,
        ];

        $consts = [
            'defaultZeiKbn' => config('consts.subject_categories.ZEI_KBN_DEFAULT')
        ];

        $view->with(compact('formSelects', 'defaultValue', 'consts', 'customCategoryCode'));
    }
}
