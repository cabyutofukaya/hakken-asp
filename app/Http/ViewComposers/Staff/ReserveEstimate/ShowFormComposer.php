<?php
namespace App\Http\ViewComposers\Staff\ReserveEstimate;

use App\Models\AccountPayable;
use App\Models\AgencyConsultation;
use App\Services\CountryService;
use App\Services\DocumentQuoteService;
use App\Services\ReserveParticipantPriceService;
use App\Services\StaffService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Traits\JsConstsTrait;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 表示ページに使う選択項目などを提供するViewComposer。見積・予約・催行済共通
 */
class ShowFormComposer
{
    use JsConstsTrait;

    public function __construct(
        CountryService $countryService,
        StaffService $staffService,
        UserCustomItemService $userCustomItemService,
        UserService $userService,
        DocumentQuoteService $documentQuoteService,
        ReserveParticipantPriceService $reserveParticipantPriceService
    ) {
        $this->countryService = $countryService;
        $this->staffService = $staffService;
        $this->userCustomItemService = $userCustomItemService;
        $this->userService = $userService;
        $this->documentQuoteService = $documentQuoteService;
        $this->reserveParticipantPriceService = $reserveParticipantPriceService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserve = Arr::get($data, 'reserve');

        ////////////////////////////////////

        $applicationStep = $reserve->application_step;

        $my = auth("staff")->user();
        $agencyId = $my->agency->id;
        $agencyAccount = $my->agency->account;

        // 初期状態で開くタブ
        $defaultTab = request()->input('tab', config('consts.reserves.DEFAULT_TAB'));
        // 相談の表示指定がある場合
        $targetConsultationNumber = request()->input('consultation_number');

        // 催行済みGETパラメータ
        $departedQuery = $reserve->is_departed ? sprintf('?%s=1', config('consts.const.DEPARTED_QUERY')) : '';

        $status = null; // ステータス値
        $reserveStatus = ''; // 状況
        if ($applicationStep === config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 見積
            $status = $reserve->estimate_status ? $reserve->estimate_status->val : null;
        } elseif ($applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約
            $status = $reserve->status ? $reserve->status->val : null;
            if ($reserve->is_departed) { // 催行済
                $reserveStatus = '催行完了';
            }
        }

        // 初期入力値。タブ毎に値をセット
        $defaultValue = [
            // 共通
            'common' => [
                'cancel_charge' => $reserve->cancel_charge, // キャンセルチャージの有無
            ],
            // 基本情報
            config('consts.reserves.TAB_BASIC_INFO') => [
                'status' => $status, // ステータス値
                'reserveStatus' => $reserveStatus, // 状況
                'updatedAt' => $reserve->updated_at->format('Y-m-dH:i:s'), // 予約情報更新日時
            ],
            config('consts.reserves.TAB_RESERVE_DETAIL') => [ // 詳細
                'sex' => config('consts.participants.DEFAULT_SEX'),
                'age_kbn' => config('consts.participants.DEFAULT_AGE_KBN'),
                'passport_issue_country_code' => config('consts.participants.DEFAULT_PASSPORT_ISSUE_COUNTRY'),
                'citizenship_code' => config('consts.participants.DEFAULT_CITIZENSHIP'),
            ],
            config('consts.reserves.TAB_CONSULTATION') => [
                'manager_id' => $my->id, // 自社担当者初期値。自分自身のID
                'status' => config('consts.agency_consultations.DEFAULT_STATUS'), // 状態
                'kind' => config('consts.agency_consultations.DEFAULT_KIND'), // 種別
            ],
        ];
        

        // 予約テーブルに設定されたカスタム項目を取得
        $userCustomItems = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
            $agencyAccount,
            true,
            [],
            [
                'user_custom_items.key',
                'user_custom_items.code',
                'user_custom_items.list',
                'user_custom_items.name',
                'user_custom_items.display_position',
                'user_custom_items.unedit_item',
            ]
        );
        if ($applicationStep === config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 見積。userCustomItemsから予約ステータス項目除去。予約/見積項目のみイレギュラーにつき
            $userCustomItems = $userCustomItems->filter(function ($row, $key) {
                return $row['code'] !== config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS');
            });
            //ステータス項目を取得
            $customStatus = $userCustomItems->firstWhere('code', config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS'));
        } elseif ($applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約。userCustomItemsから見積ステータス項目除去。予約/見積項目のみイレギュラーにつき
            $userCustomItems = $userCustomItems->filter(function ($row, $key) {
                return $row['code'] !== config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS');
            });
            //ステータス項目を取得
            $customStatus = $userCustomItems->firstWhere('code', config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'));
        } else {
            $customStatus = null;
        }


        // 当該レコードに設定されたカスタム項目値
        $vReserveCustomValues = $reserve->v_reserve_custom_values;

        // カスタム項目値とval値をセット
        $customValues = [];
        foreach ($userCustomItems as $uci) {
            $tmp = $uci->only(['key','name','display_position','code']);
            $valueRow = $vReserveCustomValues->firstWhere('key', $uci->key);
            $tmp['val'] = $valueRow ? $valueRow->val : null;
            $customValues[] = $tmp;
        }

        // カスタム項目に表示するカスタム項目を左列と右列の2つに分ける
        $row = collect($customValues)->where('display_position', config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'))->toArray();
        $customLr = array_chunk($row, ceil(count($row)/2));

        // カスタム項目値。タブ、表示位置毎に値をセット
        $customFields = [
            // 基本情報
            config('consts.reserves.TAB_BASIC_INFO') => [
                config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD') => collect($customValues)->where('display_position', config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD')),
                config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD') => [
                    //左右列に分ける
                    'left' => $customLr[0],
                    'right' => $customLr[1],
                ]
            ],
            // 詳細
            config('consts.reserves.TAB_RESERVE_DETAIL') => [],
            // 相談
            config('consts.reserves.TAB_CONSULTATION') => [],
        ];

        ////////////////////////////////

        ///// 相談履歴カスタム項目 ////

        // 相談履歴に設定されたカスタム項目を取得
        $consultationUserCustomItems = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            config('consts.user_custom_categories.CUSTOM_CATEGORY_CONSULTATION'),
            $agencyAccount,
            true,
            [],
            // [
            //     'user_custom_items.key',
            //     'user_custom_items.type',
            //     'user_custom_items.code',
            //     'user_custom_items.list',
            //     'user_custom_items.name',
            //     'user_custom_items.display_position',
            //     'user_custom_items.unedit_item',
            // ]
        );

        // form値。タブ毎に値をセット
        $formSelects = [
            // 基本情報
            config('consts.reserves.TAB_BASIC_INFO') =>
            [
                'statuses' => $customStatus ? $customStatus->select_item([''=>'---']) : [], // ステータス値
            ],
            // 詳細
            config('consts.reserves.TAB_RESERVE_DETAIL') =>
            [
                'sexes' => get_const_item('users', 'sex'), // 性別
                'ageKbns' => ['' => '-'] + get_const_item('users', 'age_kbn'), // 年齢区分
                'birthdayYears' => ['' => '年'] + $this->userService->getBirthDayYearSelect(), // 誕生日年（「YYYY => YYYY年」形式の配列）
                'birthdayMonths' => ['' => '月'] + $this->userService->getBirthDayMonthSelect(), // 誕生日月（「MM => MM月」形式の配列）
                'birthdayDays' => ['' => '日'] + $this->userService->getBirthDayDaySelect(), // 誕生日日（「DD => DD月」形式の配列）
                'countries' => ['' => '-'] + $this->countryService->getCodeNameList(), // 国名リスト
            ],
            // 相談
            config('consts.reserves.TAB_CONSULTATION') =>
            [
                'staffs' => ['' => '-'] + $this->staffService->getIdNameSelect($agencyAccount, true), // 自社スタッフ
                'statuses' => ['' => '-'] + get_const_item('agency_consultations', 'status'), // スタータス
                'kinds' => get_const_item('agency_consultations', 'kind'), // 種別
                'userCustomItems' => [
                    config('consts.user_custom_items.POSITION_CONSULTATION_CUSTOM_FIELD') => $consultationUserCustomItems->where('display_position', config('consts.user_custom_items.POSITION_CONSULTATION_CUSTOM_FIELD')),// 相談モーダル用。カスタム項目"値"ではなく"selectフォームを作成するための設定値"としてセット
                ]
            ],
        ];

        // 一覧URL
        $estimateIndexUrl = route('staff.asp.estimates.normal.index', [$agencyAccount]);
        $reserveIndexUrl = route('staff.asp.estimates.reserve.index', [$agencyAccount]);
        $departedIndexUrl = route('staff.estimates.departed.index', $agencyAccount); // 催行済
        $cancelChargeUrl = $applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE') ? route('staff.asp.estimates.reserve.cancel_charge.edit', [$agencyAccount, $reserve->control_number]) : ''; // 予約状態の場合はキャンセルチャージ

        $afterDeletedUrl = ''; // 予約情報削除後の転送先
        if ($applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約状態
            $afterDeletedUrl = $reserve->is_departed ? $departedIndexUrl : $reserveIndexUrl;
        } else { // 予約前状態
            $afterDeletedUrl = $estimateIndexUrl;
        }

        $afterCancelUrl = $applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE') ? route('staff.asp.estimates.reserve.show', [$agencyAccount, $reserve->control_number]) : ''; // キャンセル後の転送先(キャンセルチャージナシ時)

        // 各種定数値。タブ毎にセット
        $consts = [
            'common' => [
                'application_step_list' => [
                    'application_step_draft' => config('consts.reserves.APPLICATION_STEP_DRAFT'),
                    'application_step_reserve' => config('consts.reserves.APPLICATION_STEP_RESERVE'),
                ],
                'departedQuery' => $departedQuery,
                // タブコード一覧
                'tabCodes' => [
                    'tab_basic_info' => config('consts.reserves.TAB_BASIC_INFO'),
                    'tab_reserve_detail' => config('consts.reserves.TAB_RESERVE_DETAIL'),
                    'tab_consultation' => config('consts.reserves.TAB_CONSULTATION'),
                ],
                'existPurchaseData' => $this->reserveParticipantPriceService->isExistsPurchaseDataByReserveId($reserve->id, false), // 仕入情報がある場合はtrue
                'estimateIndexUrl' => $estimateIndexUrl,
                'reserveIndexUrl' => $reserveIndexUrl,
                'departedIndexUrl' => $departedIndexUrl,
                'cancelChargeUrl' => $cancelChargeUrl . $departedQuery,
                'afterDeletedUrl' => $afterDeletedUrl, // 削除後の転送先
                'afterCancelUrl' => $afterCancelUrl,
            ],
            // 基本情報
            config('consts.reserves.TAB_BASIC_INFO') =>
            [
                // 個人申し込み
                'person' => config('consts.reserves.PARTICIPANT_TYPE_PERSON'),
                // 法人申し込み
                'business' => config('consts.reserves.PARTICIPANT_TYPE_BUSINESS'),

                // カスタムフィールド表示位置
                'customFieldPositions' => [
                    'estimates_base' => config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD'), //基本情報
                    'estimates_custom' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),//カスタムフィールド
                ],
                'determineUrl' => $applicationStep === config('consts.reserves.APPLICATION_STEP_DRAFT') ? route('staff.api.asp.estimate.determine', [$agencyAccount,$reserve->estimate_number]) : null, // 見積決定URL
                'reserveIndexUrl' => $reserveIndexUrl, // 予約一覧URL
                'departedIndexUrl' => $departedIndexUrl, // 催行済み一覧URL
                'reserveEditUrl' => $applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE') ? route('staff.asp.estimates.reserve.edit', [$agencyAccount,$reserve->control_number]) : null,
                'estimateEditUrl' => $applicationStep === config('consts.reserves.APPLICATION_STEP_DRAFT') ? route('staff.asp.estimates.normal.edit', [$agencyAccount,$reserve->estimate_number]) : null,
            ],
            // 詳細
            config('consts.reserves.TAB_RESERVE_DETAIL') =>
            [
                'hasOriginalDocumentQuoteTemplate' => $this->documentQuoteService->hasOriginalDocumentQuoteTemplate($agencyId, false), // デフォルト系以外の見積・予約確認書テンプレートがある場合はtrue
            ],
            // 相談
            config('consts.reserves.TAB_CONSULTATION') =>
            [
                // ステータス値
                'statusList' => [
                    'status_reception' => config('consts.agency_consultations.STATUS_RECEPTION'),
                    'status_responding' => config('consts.agency_consultations.STATUS_RESPONDING'),
                    'status_completion' => config('consts.agency_consultations.STATUS_COMPLETION'),
                ]
            ]
        ];

        // 認可情報
        $permission = [
            // 予約
            config('consts.reserves.TAB_BASIC_INFO') => [
                'reserve_read' => Auth::user('staff')->can('view', $reserve), // 閲覧権限
                'reserve_update' => Auth::user('staff')->can('update', $reserve), // 更新権限
                'reserve_delete' => Auth::user('staff')->can('delete', $reserve), // 削除権限
            ],
            // 詳細
            config('consts.reserves.TAB_RESERVE_DETAIL') => [
                'reserve_read' => Auth::user('staff')->can('view', $reserve), // 閲覧権限
                'reserve_update' => Auth::user('staff')->can('update', $reserve), // 更新権限
                'reserve_delete' => Auth::user('staff')->can('delete', $reserve), // 削除権限
                'management_read' => Auth::user('staff')->can('viewAny', new AccountPayable), // 経理権限
                // TODO 参加者の編集権限をどうするか考えて実装する
                // 'user_create' => Auth::user('staff')->can('create', new User), // 参加者作成
            ],
            // 相談
            config('consts.reserves.TAB_CONSULTATION') => [
                'consultation_create' => Auth::user('staff')->can('create', AgencyConsultation::class), // 作成権限
                'consultation_read' => Auth::user('staff')->can('viewAny', AgencyConsultation::class), // 閲覧権限
            ]
        ];

        $flashMessage = [
            'success_message' => session('success_message'),
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact(
            'defaultValue',
            'formSelects',
            'defaultTab',
            'targetConsultationNumber',
            'permission',
            'customFields',
            'consts',
            'flashMessage',
            'jsVars',
        ));
    }
}
