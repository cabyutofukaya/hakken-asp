<?php
namespace App\Http\ViewComposers\Staff\Subject\Option;

use Illuminate\View\View;
use App\Services\SubjectCategoryService;
use App\Services\CityService;
use App\Services\SupplierService;
use App\Services\UserCustomItemService;
use Illuminate\Support\Arr;
use App\Traits\SubjectTrait;

/**
 * オプション科目編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use SubjectTrait;
    
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
        $data = $view->getData(); // controllerにセットされたデータを取得
        $subjectOption = Arr::get($data, 'subjectOption');

        ///////////////////////////////
        $agencyAccount = request()->agencyAccount;

        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT');

        $defaultValue = session()->getOldInput();

        // カスタム項目を取得
        $userCustomItems = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT'), 
            $agencyAccount, 
            true, 
            [], 
            [], 
            [
                'display_position' => config('consts.user_custom_items.POSITION_SUBJECT_OPTION')
            ]
        );

        //////////////// form初期値を設定 ////////////////
        // 基本項目
        foreach (['subject_option_kbn_id','code','name','city_id','supplier_id','ad_gross_ex','ad_gross','ad_cost','ad_commission_rate','ad_net','ad_zei_kbn','ad_gross_profit','ch_gross_ex','ch_gross','ch_cost','ch_commission_rate','ch_net','ch_zei_kbn','ch_gross_profit','inf_gross_ex','inf_gross','inf_cost','inf_commission_rate','inf_net','inf_zei_kbn','inf_gross_profit','note',
        ] as $f) {
            $defaultValue[$f] = old($f, data_get($subjectOption, $f));
        }

        // 当該レコードに設定されたカスタム項目値
        $vSubjectOptionCustomValues = $subjectOption->v_subject_option_custom_values;
        foreach ($userCustomItems->pluck('key') as $key) {
            $row = $vSubjectOptionCustomValues->firstWhere('key', $key);
            $defaultValue[$key] = old($key, Arr::get($row, 'val')); // val値をセット
        }

        $formSelects = [
            'subjectCategories' => $this->subjectCategoryService->all()->pluck('name', 'code')->toArray(),
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
