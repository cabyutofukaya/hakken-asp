<?php
namespace App\Http\ViewComposers\Admin\Agency;

use App\Models\Agency;
use Illuminate\View\View;
use Illuminate\Support\Arr;
use App\Services\ContractPlanService;
use App\Services\AgencyService;
use App\Services\PrefectureService;


/**
 * 会社情報作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    public function __construct(AgencyService $agencyService, ContractPlanService $contractPlanService, PrefectureService $prefectureService)
    {
        $this->contractPlanService = $contractPlanService;
        $this->agencyService = $agencyService;
        $this->prefectureService = $prefectureService;
    }
    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $statuses = get_const_item('agencies', 'status');
        $businessScopes = get_const_item('agencies', 'business_scope');
        $registrationTypes = get_const_item('agencies', 'registration_type');
        $travelAgencyAssociations = get_const_item('agencies', 'travel_agency_association');

        $prefectureCodes = $this->prefectureService->getCodeNameList();

        $contractPlans = $this->contractPlanService->getList();

        $formSelects = [
            'statuses' => $statuses,
            'businessScopes' => $businessScopes,
            'registrationTypes' => $registrationTypes,
            'travelAgencyAssociations' => $travelAgencyAssociations,
            'prefectureCodes' => $prefectureCodes,
            'contractPlans' => $contractPlans,
        ];

        $consts = [
            'agencyIndexUrl' => route('admin.agencies.index'),
        ];

        $view->with(compact('formSelects', 'statuses', 'businessScopes', 'registrationTypes', 'travelAgencyAssociations', 'prefectureCodes', 'contractPlans','consts'));
    }
}
