<?php
namespace App\Http\ViewComposers\Staff\Web\ReserveConfirm;

use App\Services\BusinessUserManagerService;
use App\Services\CityService;
use App\Services\DocumentCommonService;
use App\Services\DocumentQuoteService;
use App\Services\ParticipantService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\WebReserveService;
use App\Services\SubjectAirplaneService;
use App\Services\SubjectHotelService;
use App\Services\SubjectOptionService;
use App\Services\SupplierService;
use App\Services\UserCustomItemService;
use App\Traits\BusinessFormTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * PDFページで使う選択項目などを提供するViewComposer
 */
class PdfComposer
{
    use BusinessFormTrait;

    public function __construct(WebReserveService $webReserveService, UserCustomItemService $userCustomItemService, SupplierService $supplierService, CityService $cityService, SubjectHotelService $subjectHotelService, SubjectOptionService $subjectOptionService, SubjectAirplaneService $subjectAirplaneService, DocumentCommonService $documentCommonService, DocumentQuoteService $documentQuoteService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, BusinessUserManagerService $businessUserManagerService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService, ParticipantService $participantService)
    {
        $this->cityService = $cityService;
        $this->webReserveService = $webReserveService;
        $this->supplierService = $supplierService;
        $this->userCustomItemService = $userCustomItemService;
        $this->subjectHotelService = $subjectHotelService;
        $this->subjectOptionService = $subjectOptionService;
        $this->subjectAirplaneService = $subjectAirplaneService;
        $this->documentCommonService = $documentCommonService;
        $this->documentQuoteService = $documentQuoteService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->participantService = $participantService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserveConfirm = Arr::get($data, 'reserveConfirm');

        //////////////////////////////////

        $agencyAccount = request()->agencyAccount;

        // 受付種別
        $reception = config('consts.const.RECEPTION_TYPE_WEB');

        // キャンセルか否か
        $isCanceled = $reserveConfirm->reserve->is_canceled;

        foreach ([
            'control_number',
            'confirm_number',
            'issue_date',
            'document_quote_id',
            'document_common_id',
            'document_address',
            'name',
            'departure_date',
            'return_date',
            'manager',
            'representative',
            'participant_ids',
            'document_setting',
            'document_common_setting',
            'amount_total',
            'status'
        ] as $f) {
            $value[$f] = $reserveConfirm->{$f};
        }

        // 参加者情報
        $participantInfo = $this->webReserveService->getParticipants($reserveConfirm->reserve_itinerary->reserve_id, true); // 参加者一覧（取消者を含む。削除済は取得しない）
        // 参加者情報
        $participants = []; // 参加者リスト
        foreach ($participantInfo as $participant) {
            // 取り消し情報もセットする
            if (in_array($participant->id, $value['participant_ids'], true)) {
                $participants[] = $participant->only(['id','name','user_number','sex','name_roman','cancel']);
            }
        }

        // 税区分。表示設定で「非課税/不課税」がOffになっている場合はプロパティを削除
        if (!in_array("消費税_非課税/不課税", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'), []))) {
            $zeiKbns = get_const_item('subject_categories', 'document_zei_kbn');
            foreach ([
                config('consts.subject_categories.ZEI_KBN_TAX_FREE'),
                config('consts.subject_categories.ZEI_KBN_NON_TAX')
                ] as $k) {
                unset($zeiKbns[$k]);
            }
        } else {
            $zeiKbns = get_const_item('subject_categories', 'document_zei_kbn');
        }

        $formSelects = [
            'zeiKbns' => $zeiKbns,
            'participants' => $participants, // 厳密に言うと書類作成時の参加者情報ではないが保存後、すぐにpdf作成しているのでほぼ書類作成時と同じ内容と考えて差し支えない
            'honorifics' => get_const_item('documents', 'honorific'), // 敬称
            'statuses' => get_const_item('reserve_confirms', 'status'), // ステータス
            'setting' => [
                config('consts.document_quotes.DISPLAY_BLOCK') => $this->structuralChange(config('consts.document_quotes.DISPLAY_BLOCK_LIST')),
                config('consts.document_quotes.RESERVATION_INFO') => $this->structuralChange(config('consts.document_quotes.RESERVATION_INFO_LIST')),
                config('consts.document_quotes.AIR_TICKET_INFO') => $this->structuralChange(config('consts.document_quotes.AIR_TICKET_INFO_LIST')),
                config('consts.document_quotes.BREAKDOWN_PRICE') => $this->structuralChange(config('consts.document_quotes.BREAKDOWN_PRICE_LIST'))
            ],
        ];


        //////// 料金、ホテル情報

        list($optionPrices, $airticketPrices, $hotelPrices, $hotelInfo, $hotelContacts) = $this->getPriceAndHotelInfoPdf($reserveConfirm, $value['participant_ids'], $isCanceled);

        // 数量をまとめた配列を取得（オプション科目/航空券科目/ホテル科目）
        $optionPriceBreakdown = $this->getOptionPriceBreakdown($optionPrices);
        $airticketPriceBreakdown = $this->getAirticketPriceBreakdown($airticketPrices);
        $hotelPriceBreakdown = $this->getHotelPriceBreakdown($hotelPrices);

        $view->with(compact('reception', 'value', 'formSelects', 'hotelContacts', 'hotelInfo', 'optionPrices', 'airticketPrices', 'hotelPrices', 'optionPriceBreakdown', 'airticketPriceBreakdown', 'hotelPriceBreakdown', 'isCanceled'));
    }
}
