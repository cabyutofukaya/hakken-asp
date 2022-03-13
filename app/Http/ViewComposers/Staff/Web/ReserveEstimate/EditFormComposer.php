<?php
namespace App\Http\ViewComposers\Staff\Web\ReserveEstimate;

use App\Services\CountryService;
use App\Services\MasterAreaService;
use App\Services\PrefectureService;
use App\Services\ReserveService;
use App\Services\StaffService;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Services\VAreaService;
use App\Traits\ConstsTrait;
use App\Traits\JsConstsTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 編集ページで使う選択項目などを提供するViewComposer
 * 見積・予約共通
 */
class EditFormComposer
{
    use UserCustomItemTrait, ConstsTrait, JsConstsTrait;

    public function __construct(MasterAreaService $masterAreaService, ReserveService $reserveService, StaffService $staffService, UserCustomItemService $userCustomItemService, UserCustomCategoryService $userCustomCategoryService, VAreaService $vAreaService, CountryService $countryService, UserService $userService, PrefectureService $prefectureService)
    {
        $this->masterAreaService = $masterAreaService;
        $this->reserveService = $reserveService;
        $this->staffService = $staffService;
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->userCustomItemService = $userCustomItemService;
        $this->vAreaService = $vAreaService;
        $this->countryService = $countryService;
        $this->prefectureService = $prefectureService;
        $this->userService = $userService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserve = Arr::get($data, 'reserve');

        //////////////////////////////////

        $applicationStep = $reserve->application_step;

        $my = auth("staff")->user();
        $agencyId = $my->agency->id;
        $agencyAccount = $my->agency->account;

        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'); // カスタムカテゴリcode

        $isCanceled = $reserve->is_canceled; // キャンセル予約か否か

        // デフォルトデータを作成
        $defaultValue = session()->getOldInput();
        foreach ($reserve->toArray() as $k => $v) {
            if (!isset($defaultValue[$k])) {
                if ($k === 'updated_at') { // 更新日時はformatをYYYY-MM-DD HH:II:SSに
                    $defaultValue[$k] = $reserve->updated_at->format('Y-m-d H:i:s');
                } else {
                    $defaultValue[$k] = data_get($reserve, $k);
                }
            }
        }

        // 申込者情報（Webユーザーなので種別は個人で名前等は編集不可）
        $applicant = [];
        $applicant['participant_type'] = optional($reserve->applicantable)->applicant_type;
        $applicant['applicant_user_number'] = optional($reserve->applicantable)->user_number;
        $applicant['is_profile_complete'] = optional($reserve->applicantable->userable)->is_profile_complete;
        $applicant['name'] = optional($reserve->applicantable->userable)->name;
        $applicant['org_name'] = optional($reserve->applicantable->userable)->org_name;
        $applicant['name_kana'] = optional($reserve->applicantable->userable)->name_kana;
        $applicant['name_roman'] = optional($reserve->applicantable->userable)->name_roman;
        $applicant['sex_label'] = optional($reserve->applicantable->userable)->sex_label;
        $applicant['age_calc'] = optional($reserve->applicantable->userable)->age_calc;
        $applicant['age_kbn_label'] = $reserve->applicantable->userable ? optional($reserve->applicantable->userable->user_ext)->age_kbn_label : null;

        //////////// カスタム項目値設定 ////////////

        // 当該マスタに設定されたカスタム項目を取得
        $userCustomItems = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            $customCategoryCode,
            $agencyAccount,
            true
        );
        if ($applicationStep === config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 見積。userCustomItemsから予約ステータス項目除去。予約/見積項目のみイレギュラーにつき
            $userCustomItems = $userCustomItems->filter(function ($row, $key) {
                return $row['code'] !== config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS');
            });
        } elseif ($applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約。userCustomItemsから見積ステータス項目除去。予約/見積項目のみイレギュラーにつき
            $userCustomItems = $userCustomItems->filter(function ($row, $key) {
                return $row['code'] !== config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS');
            });
        }

        // 当該レコードに設定されたカスタム項目値
        $vReserveCustomValues = $reserve->v_reserve_custom_values;
        foreach ($userCustomItems->pluck('key') as $key) {
            $row = $vReserveCustomValues->firstWhere('key', $key);
            $defaultValue[$key] = old($key, Arr::get($row, 'val')); // val値をセット
        }
        ////////////

        if (Arr::get($defaultValue, "departure_id")) { // 出発地IDがある場合は初期値用に名称等も取得
            $defaultValue['departure'] = $this->vAreaService->getDefaultSelectRow($defaultValue['departure_id']);
        }
        if (Arr::get($defaultValue, "destination_id")) { // 目的地IDがある場合は初期値用に名称等も取得
            $defaultValue['destination'] = $this->vAreaService->getDefaultSelectRow($defaultValue['destination_id']);
        }

        $formSelects = [
            'staffs' => ['' => '---'] + $this->staffService->getIdNameSelectSafeValues($agencyId, [$reserve->manager_id]), // 自社スタッフ
            'participantTypes' => get_const_item('reserves', 'participant_type'),
            'defaultAreas' => $this->masterAreaService->getDefaultOptions(['label' => '---', 'value' => '']),
            'countries' => ['' => '-'] + $this->countryService->getCodeNameList(), // 国名リスト
            'sexes' => get_const_item('users', 'sex'), // 性別
            'ageKbns' => ['' => '-'] + get_const_item('users', 'age_kbn'), // 年齢区分
        ];

        $consts = [
            // 個人・法人種別
            'customerKbns' => [
                'person' => config('consts.reserves.PARTICIPANT_TYPE_PERSON'),
                'business' => config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')
            ],
            // カスタムフィールド表示位置
            'customFieldPositions' => [
                'base' => config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD'),
                'custom' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD')
            ],
            // カスタム項目管理コード
            'customFieldCodes' => [
                'travel_type' => config('consts.user_custom_items.CODE_APPLICATION_TRAVEL_TYPE')
            ],
            'reserveUpdateUrl' => $applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE') ? route('staff.web.estimates.reserve.update', [$agencyAccount, $reserve->control_number]) : null,
        ];

        // カスタム項目。表示位置毎に値をセット
        $customFields = [
            config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD')), // 基本情報
            config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD')), // カスタムフィールド
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('applicationStep', 'defaultValue', 'formSelects', 'consts', 'customCategoryCode', 'customFields', 'jsVars', 'applicant', 'isCanceled'));
    }
}
