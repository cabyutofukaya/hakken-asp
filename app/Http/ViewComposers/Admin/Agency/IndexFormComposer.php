<?php
namespace App\Http\ViewComposers\Admin\Agency;

use App\Models\Agency;
use Illuminate\View\View;
use Illuminate\Support\Arr;
use App\Services\ContractPlanService;
use App\Services\AgencyService;
use App\Services\PrefectureService;


/**
 * トップページに使う選択項目などを提供するViewComposer
 */
class IndexFormComposer
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
        $fairTradeCouncils = [0=>'なし',1=>'あり'];
        $iatas = [0=>'なし',1=>'あり'];
        $etbts = [0=>'なし',1=>'あり'];
        $bondGuarantees = [0=>'なし',1=>'あり'];
        
        $view->with(compact('statuses', 'businessScopes', 'registrationTypes', 'travelAgencyAssociations', 'fairTradeCouncils','iatas','etbts','bondGuarantees'));
    }
}
