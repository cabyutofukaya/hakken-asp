<?php
namespace App\Http\ViewComposers\Staff\ReserveBundleInvoice;

use App\Services\CityService;
use App\Services\ReserveService;
use App\Services\SupplierService;
use App\Services\SubjectHotelService;
use App\Services\SubjectOptionService;
use App\Services\SubjectAirplaneService;
use App\Services\UserCustomItemService;
use App\Services\DocumentCommonService;
use App\Services\DocumentRequestAllService;
use App\Services\BusinessUserManagerService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveInvoiceService;
use App\Services\BusinessUserService;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use App\Traits\JsConstsTrait;
use App\Traits\BusinessFormTrait;

/**
 * 作成ページに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use JsConstsTrait, BusinessFormTrait;

    public function __construct(
        ReserveService $reserveService,
        UserCustomItemService $userCustomItemService,
        SupplierService $supplierService,
        CityService $cityService,
        SubjectHotelService $subjectHotelService,
        SubjectOptionService $subjectOptionService,
        SubjectAirplaneService $subjectAirplaneService,
        DocumentCommonService $documentCommonService,
        DocumentRequestAllService $documentRequestAllService,
        ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService,
        BusinessUserManagerService $businessUserManagerService,
        ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService,
        ReserveInvoiceService $reserveInvoiceService,
        BusinessUserService $businessUserService
    ) {
        $this->cityService = $cityService;
        $this->reserveService = $reserveService;
        $this->supplierService = $supplierService;
        $this->userCustomItemService = $userCustomItemService;
        $this->subjectHotelService = $subjectHotelService;
        $this->subjectOptionService = $subjectOptionService;
        $this->subjectAirplaneService = $subjectAirplaneService;
        $this->documentCommonService = $documentCommonService;
        $this->documentRequestAllService = $documentRequestAllService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->businessUserService = $businessUserService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserveBundleInvoice = Arr::get($data, 'reserveBundleInvoice');

        $reserveBundleInvoiceId = $reserveBundleInvoice->id;
        //////////////////////////////////

        $my = auth("staff")->user();
        $agencyId = $my->agency_id;
        $agencyAccount = $my->agency->account;


        // 先方担当者情報（削除済も取得）
        $partnerManagersInfo = $this->businessUserService->getManagers($reserveBundleInvoice->business_user_id, true);


        // 担当者情報
        $partnerManagers = []; // 担当者リスト
        foreach ($partnerManagersInfo as $partnerManager) {
            // 削除情報もセットする
            $partnerManagers[] = $partnerManager->only(['id','org_name','user_number','sex','name_roman','deleted_at']);
        }

        $documentRequestAllId = $reserveBundleInvoice->document_setting['id'] ?? '';
        $documentCommonId = $reserveBundleInvoice->document_common_id ?? '';

        // 共通設定
        $documentCommonSetting = $reserveBundleInvoice->document_common_setting;

        //　書類設定。未設定なら念のため初期化
        $documentSetting = $this->getDocumentSettingSealOrInitSetting($reserveBundleInvoice->document_setting);

        // 「検印欄」の表示・非表示は設定がイレギュラーにつき、他の設定項目と形式を合わせる
        $this->setSealSetting($documentSetting, config('consts.document_request_alls.DISPLAY_BLOCK'));

        $updatedAt = $reserveBundleInvoice->updated_at->format('Y-m-d H:i:s'); // 同時編集の判定に使用

        /////////// 入力初期値をセット ///////////
        $bundleInvoiceNumber = $reserveBundleInvoice->user_bundle_invoice_number;
        // 発行日
        $issueDate = $reserveBundleInvoice->issue_date ?? date('Y/m/d');
        // 支払い期限
        $paymentDeadline = $reserveBundleInvoice->payment_deadline;
        // 案件名
        $name = $reserveBundleInvoice->name;
        // 期間開始
        $periodFrom = $reserveBundleInvoice->period_from;
        // 期間終了
        $periodTo = $reserveBundleInvoice->period_to;
        // 担当
        $manager = $reserveBundleInvoice->manager;

        $partnerManagerIds = $reserveBundleInvoice->partner_manager_ids; // チェックONの担当者ID

        // 宛名情報
        $documentAddress = $reserveBundleInvoice->document_address ?? $this->getDocumentAddressByBusinessUser($reserveBundleInvoice->business_user); // 申込者情報が設定されていない場合は作成（書類作成後、申込者を変更した場合、本カラムはリセットされている）。

        $businessUserId = $reserveBundleInvoice->business_user_id; // 法人顧客ID

        $status = $reserveBundleInvoice->status;

        ///////////////// テンプレートセレクトメニュー /////////////////

        // 編集時は論理削除を考慮してselectメニューを取得
        $documentRequestAlls = ['' => '---'] + $this->documentRequestAllService->getIdNameSelectSafeValues($agencyId, [$documentRequestAllId]);


        // 各種デフォルト
        $defaultValue = [
            'id' => $reserveBundleInvoiceId,
            'business_user_id' => $businessUserId, // 法人顧客ID
            'document_request_all_id' => $documentRequestAllId, // 書類設定ID
            'document_common_id' => $documentCommonId, // 共通書類設定ID
            'user_bundle_invoice_number' => $bundleInvoiceNumber, // 見積番号
            'issue_date' => $issueDate, // 発行日
            'payment_deadline' => $paymentDeadline, // 支払い期限
            'name' => $name, // 案件名
            'period_from' => $periodFrom, // 期間開始
            'period_to' => $periodTo, // 期間終了
            'manager' => $manager, // 担当
            'partner_manager_ids' => $partnerManagerIds, // チェックONの担当者ID
            'document_address' => $documentAddress, // 宛名情報
            'updated_at' => $updatedAt,
            'status' => $status, // 書類ステータス
        ];

        $formSelects = [
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelectSafeValues($agencyId, [$documentCommonId]), // 宛名/自社情報共通設定selectメニュー
            'documentRequestAlls' => $documentRequestAlls,
            'partnerManagers' => $partnerManagers,
            'honorifics' => get_const_item('documents', 'honorific'), // 敬称
            'statuses' => get_const_item('reserve_bundle_invoices', 'status'), // ステータス
            'setting' => [
                config('consts.document_request_alls.DISPLAY_BLOCK') => $this->structuralChange(config('consts.document_request_alls.DISPLAY_BLOCK_LIST')),
                config('consts.document_request_alls.RESERVATION_INFO') => $this->structuralChange(config('consts.document_request_alls.RESERVATION_INFO_LIST')),
                config('consts.document_request_alls.BREAKDOWN_PRICE') => $this->structuralChange(config('consts.document_request_alls.BREAKDOWN_PRICE_LIST'))
            ],
        ];

        $consts = $this->getConstDatas();

        // 予約価格情報を取得
        $reserveInvoices = $this->reserveInvoiceService->getByReserveBundleInvoiceId(
            $agencyAccount, 
            $reserveBundleInvoiceId, 
            ['reserve:id,control_number,applicantable_type,applicantable_id,cancel_at','reserve.applicantable:id,name'],
            ['reserve_id','option_prices','airticket_prices','hotel_prices','participant_ids'],
            false
        );

        list($reservePrices, $reserveCancelInfo) = $this->getReservePriceInfo($reserveInvoices);

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('reserveBundleInvoiceId', 'defaultValue', 'formSelects', 'consts', 'documentCommonSetting', 'reservePrices', 'reserveCancelInfo', 'documentSetting', 'jsVars'));
    }
}
