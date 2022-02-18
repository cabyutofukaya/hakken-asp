<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveItineraryEnabledRequest;
use App\Http\Requests\Staff\SubjectSearchRequest;
use App\Http\Resources\Staff\ReserveItinerary\UpdateResource;
use App\Http\Resources\Staff\Subject\AirplaneIndexResource;
use App\Http\Resources\Staff\Subject\HotelIndexResource;
use App\Http\Resources\Staff\Subject\OptionIndexResource;
use App\Models\ReserveItinerary;
use App\Services\ReserveService;
use App\Services\SubjectAirplaneService;
use App\Services\SubjectHotelService;
use App\Services\SubjectOptionService;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;

/**
 * 科目
 */
class SubjectController extends Controller
{
    public function __construct(SubjectOptionService $subjectOptionService, SubjectHotelService $subjectHotelService, SubjectAirplaneService $subjectAirplaneService)
    {
        $this->subjectOptionService = $subjectOptionService;
        $this->subjectHotelService = $subjectHotelService;
        $this->subjectAirplaneService = $subjectAirplaneService;
    }

    /**
     * 科目検索
     *
     * SubjectOptionなどに実装した方が良い気もするど、行程作成に関する内容なので
     * とりあず本コントローラに実装
     */
    public function search(SubjectSearchRequest $request, $agencyAccount)
    {
        // 認可チェックまですると負担が大きそうなので、ひとまず実装ナシ

        $subjectCategory = $request->input("subject_category");
        $str = $request->input("word");

        switch ($subjectCategory) {
            case config('consts.subject_categories.SUBJECT_CATEGORY_OPTION'):
                return OptionIndexResource::collection($this->subjectOptionService->search($agencyAccount, $str, ['supplier','v_subject_option_custom_values'], [], 100));
            case config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE'):
                return AirplaneIndexResource::collection($this->subjectAirplaneService->search($agencyAccount, $str, ['supplier','v_subject_airplane_custom_values'], [], 100));
                break;
            case config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL'):
                return HotelIndexResource::collection($this->subjectHotelService->search($agencyAccount, $str, ['supplier','v_subject_hotel_custom_values'], [], 100));
                break;
            default:
                return [];
        }
    }
}
