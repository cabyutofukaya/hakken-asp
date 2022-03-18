<?php
namespace App\Http\ViewComposers\Staff\Web\ReserveInvoice;

use App\Services\CityService;
use App\Services\WebReserveService;
use App\Services\SupplierService;
use App\Services\SubjectHotelService;
use App\Services\SubjectOptionService;
use App\Services\SubjectAirplaneService;
use App\Services\UserCustomItemService;
use App\Services\DocumentCommonService;
use App\Services\DocumentRequestService;
use App\Services\BusinessUserManagerService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveInvoiceService;
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
        WebReserveService $webReserveService,
        UserCustomItemService $userCustomItemService,
        SupplierService $supplierService,
        CityService $cityService,
        SubjectHotelService $subjectHotelService,
        SubjectOptionService $subjectOptionService,
        SubjectAirplaneService $subjectAirplaneService,
        DocumentCommonService $documentCommonService,
        DocumentRequestService $documentRequestService,
        ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService,
        BusinessUserManagerService $businessUserManagerService,
        ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService,
        ReserveInvoiceService $reserveInvoiceService
    ) {
        $this->cityService = $cityService;
        $this->webReserveService = $webReserveService;
        $this->supplierService = $supplierService;
        $this->userCustomItemService = $userCustomItemService;
        $this->subjectHotelService = $subjectHotelService;
        $this->subjectOptionService = $subjectOptionService;
        $this->subjectAirplaneService = $subjectAirplaneService;
        $this->documentCommonService = $documentCommonService;
        $this->documentRequestService = $documentRequestService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->reserveInvoiceService = $reserveInvoiceService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserve = Arr::get($data, 'reserve', null);
        $reserveInvoice = Arr::get($data, 'reserveInvoice', null);

        //////////////////////////////////

        $my = auth("staff")->user();
        $agencyId = $my->agency_id;
        $agencyAccount = $my->agency->account;

        // 受付種別
        $reception = config('consts.const.RECEPTION_TYPE_WEB');

        $applicationStep = $reserve->application_step;

        // 催行済みか否か
        $isDeparted = $reserve->is_departed;

        // キャンセルか否か
        $isCanceled = $reserve->is_canceled;

        // 参加者情報
        $participantInfo = $this->webReserveService->getParticipants($reserve->id, true); // 参加者一覧（取消者を含む。削除済は取得しない）

        $reserveUpdatedAt = $reserve->updated_at->format('Y-m-d H:i:s'); // 同時編集の判定に使用

        // 請求書、共通設定のデフォルト設定情報、予約番号
        if ($reserveInvoice) { // 請求書保存データあり

            // 参加者情報
            $participants = []; // 参加者リスト
            foreach ($participantInfo as $participant) {
                // 取り消し情報もセットする
                $participants[] = $participant->only(['id','name','user_number','sex','name_roman','cancel']);
            }

            $documentRequestId = $reserveInvoice->document_setting['id'] ?? '';
            $documentCommonId = $reserveInvoice->document_common_id ?? '';

            // 共通設定
            $documentCommonSetting = $reserveInvoice->document_common_setting;

            $documentSetting = $reserveInvoice->document_setting;

            /////////// 入力初期値をセット ///////////
            $invoiceNumber = $reserveInvoice->user_invoice_number;
            // 発行日
            $issueDate = $reserveInvoice->issue_date ?? date('Y/m/d');
            // 支払い期限
            $paymentDeadline = $reserveInvoice->payment_deadline;
            // 案件名
            $name = $reserveInvoice->name;
            // 出発日
            $departureDate = $reserveInvoice->departure_date;
            // 帰着日
            $returnDate = $reserveInvoice->return_date;
            // 担当
            $manager = $reserveInvoice->manager;
            // 代表者情報
            $representative = $reserveInvoice->representative;

            $participantIds = $reserveInvoice->participant_ids; // チェックONの参加者ID

            // 申込者情報
            $documentAddress = $reserveInvoice->document_address ?? $this->getDocumentAddress($reserve->applicantable); // 申込者情報が設定されていない場合は予約情報から取得（書類作成後、申込者を変更した場合、本カラムはリセットされている）。

            $businessUserId = null; // 法人顧客ID
            if ($reserve->applicantable && $reserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {
                $businessUserId = $reserve->applicantable->business_user_id;
            }

            $status = $reserveInvoice->status;

            ///////////////// テンプレートセレクトメニュー /////////////////

            // 編集時は論理削除を考慮してselectメニューを取得
            $documentRequests = ['' => '---'] + $this->documentRequestService->getIdNameSelectSafeValues($agencyId, [$documentRequestId]);
        } else {
            // 請求書の基本データは予約作成時に作られるのでここの処理に来ることは無いはず

            // 参加者情報
            $participants = []; // 参加者リスト
            foreach ($participantInfo as $participant) {
                // 取り消し情報もセットする
                $participants[] = $participant->only(['id','name','user_number','sex','name_roman','cancel']);    
            }

            // checkオンの参加者IDリスト（作成時は取消者以外を全てONに）
            $participantIds = $this->getDefaultParticipantCheckIds($participantInfo);


            $documentRequest = $this->documentRequestService->getDefault($agencyId);
            $documentRequestId = $documentRequest->id;
            $documentCommonId = $documentRequest->document_common_id ?? "";
            
            // 共通設定
            $documentCommonSetting = $documentRequest ? $documentRequest->document_common->toArray() : [];

            // 書類設定。$documentRequestが未設定なればsetting配列、sealプロパティ初期化
            $documentSetting = $this->getDocumentSettingSealOrInitSetting($documentRequest->toArray());
            
            // 「検印欄」の表示・非表示は設定がイレギュラーにつき、他の設定項目と形式を合わせる
            $this->setSealSetting($documentSetting, config('consts.document_requests.DISPLAY_BLOCK'));

            /////////// 入力初期値をセット ///////////
            $invoiceNumber = "";
            // 発行日
            $issueDate = date('Y/m/d');
            // 支払い期限
            $paymentDeadline = '';
            // 案件名
            $name = $reserve->name;
            // 出発日
            $departureDate = $reserve->departure_date;
            // 帰着日
            $returnDate = $reserve->return_date;
            // 担当
            $manager = $reserve->manager->name;
            // 代表者情報
            $representative = $this->getRepresentativeInfo($reserve);
            // 申込者情報
            $documentAddress = $this->getDocumentAddress($reserve->applicantable);

            $businessUserId = null; // 法人顧客ID
            if ($reserve->applicantable && $reserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {
                $businessUserId = $reserve->applicantable->business_user_id;
            }

            $status = config('consts.reserve_invoices.STATUS_DEFAULT');

            ///////////////// テンプレートセレクトメニュー /////////////////

            // 新規作成時は削除済みテンプレートは取得しない
            $documentRequests = ['' => '---'] + $this->documentRequestService->getIdNameSelect($agencyId);
        }

        // 各種デフォルト
        $defaultValue = [
            'business_user_id' => $businessUserId, // 法人顧客ID
            'document_request_id' => $documentRequestId, // 書類設定ID
            'document_common_id' => $documentCommonId, // 共通書類設定ID
            'user_invoice_number' => $invoiceNumber, // 見積番号
            'issue_date' => $issueDate, // 発行日
            'payment_deadline' => $paymentDeadline, // 支払い期限
            'name' => $name, // 案件名
            'departure_date' => $departureDate, // 出発日
            'return_date' => $returnDate, // 帰着日
            'manager' => $manager, // 担当
            'representative' => $representative, // 代表者情報
            'participant_ids' => $participantIds, // チェックONの参加者ID
            'document_address' => $documentAddress, // 宛名情報
            'status' => $status, // 書類ステータス
            'reserve' => [
                'updated_at' => $reserveUpdatedAt,
            ],
        ];

        $formSelects = [
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelectSafeValues($agencyId, [$documentCommonId]), // 宛名/自社情報共通設定selectメニュー
            'documentRequests' => $documentRequests,
            'participants' => $participants,
            'honorifics' => get_const_item('documents', 'honorific'), // 敬称
            'statuses' => get_const_item('reserve_invoices', 'status'), // ステータス
            'setting' => [
                config('consts.document_requests.DISPLAY_BLOCK') => $this->structuralChange(config('consts.document_requests.DISPLAY_BLOCK_LIST')),
                config('consts.document_requests.RESERVATION_INFO') => $this->structuralChange(config('consts.document_requests.RESERVATION_INFO_LIST')),
                config('consts.document_requests.AIR_TICKET_INFO') => $this->structuralChange(config('consts.document_requests.AIR_TICKET_INFO_LIST')),
                config('consts.document_requests.BREAKDOWN_PRICE') => $this->structuralChange(config('consts.document_requests.BREAKDOWN_PRICE_LIST'))
            ],
        ];

        $consts = $this->getConstDatas();
        $consts['departedIndexUrl'] = route('staff.estimates.departed.index', $agencyAccount); // 催行済URL

        // 各種URL。ASP or WEB申し込みで出し分け
        $consts['reserveIndexUrl'] = '';
        $consts['reserveUrl'] = '';

        if (optional($reserve)->reception_type == config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $consts['reserveIndexUrl'] = route('staff.asp.estimates.reserve.index', [$agencyAccount]);
            $consts['reserveUrl'] = route('staff.asp.estimates.reserve.show', [$agencyAccount, optional($reserve)->control_number ?? '']);

        } elseif (optional($reserve)->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $consts['reserveIndexUrl'] = route('staff.web.estimates.reserve.index', [$agencyAccount]);
            $consts['reserveUrl'] = route('staff.web.estimates.reserve.show', [$agencyAccount, optional($reserve)->control_number ?? '']);

        }


        // オプション価格情報、航空券価格情報、ホテル価格情報、宿泊施設情報、宿泊施設連絡先を取得
        list($optionPrices, $airticketPrices, $hotelPrices, $hotelInfo, $hotelContacts) = $this->getPriceAndHotelInfo($reserve->enabled_reserve_itinerary->id ? $reserve->enabled_reserve_itinerary : null, $isCanceled, false);

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('applicationStep', 'defaultValue', 'formSelects', 'consts', 'documentCommonSetting', 'hotelContacts', 'hotelInfo', 'optionPrices', 'airticketPrices', 'hotelPrices', 'documentSetting', 'jsVars', 'reception', 'isDeparted', 'isCanceled'));
    }
}
