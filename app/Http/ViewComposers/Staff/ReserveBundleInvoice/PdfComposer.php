<?php
namespace App\Http\ViewComposers\Staff\ReserveBundleInvoice;

use App\Services\BusinessUserManagerService;
use App\Services\CityService;
use App\Services\DocumentCommonService;
use App\Services\DocumentQuoteService;
use App\Services\ParticipantService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveService;
use App\Services\SubjectAirplaneService;
use App\Services\SubjectHotelService;
use App\Services\SubjectOptionService;
use App\Services\SupplierService;
use App\Services\UserCustomItemService;
use App\Services\BusinessUserService;
use App\Traits\BusinessFormTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * PDFページで使う選択項目などを提供するViewComposer
 */
class PdfComposer
{
    use BusinessFormTrait;

    public function __construct(ReserveService $reserveService, UserCustomItemService $userCustomItemService, SupplierService $supplierService, CityService $cityService, SubjectHotelService $subjectHotelService, SubjectOptionService $subjectOptionService, SubjectAirplaneService $subjectAirplaneService, DocumentCommonService $documentCommonService, DocumentQuoteService $documentQuoteService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, BusinessUserManagerService $businessUserManagerService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService, ParticipantService $participantService, BusinessUserService $businessUserService)
    {
        $this->cityService = $cityService;
        $this->reserveService = $reserveService;
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

        //////////////////////////////////

        foreach ([
            'user_bundle_invoice_number',
            'issue_date',
            'payment_deadline',
            'period_from',
            'period_to',
            'document_request_all_id',
            'document_common_id',
            'billing_address_name',
            'document_address',
            'name',
            // 'partner_manager_ids',
            'document_setting',
            'document_common_setting',
            'amount_total',
            'status',
        ] as $f) {
            $value[$f] = $reserveBundleInvoice->{$f};
        }

        // 先方担当者情報（削除済も取得）
        $partnerManagersInfo = $this->businessUserService->getManagers($reserveBundleInvoice->business_user_id, true);

        // 担当者情報
        $partnerManagers = []; // 担当者リスト
        foreach ($partnerManagersInfo as $partnerManager) {
            // 削除情報もセットする
            if (in_array($partnerManager->id, $reserveBundleInvoice->partner_manager_ids, true)) {
                $partnerManagers[$partnerManager->id] = $partnerManager->only(['id','org_name','user_number','sex','name_roman','deleted_at']);
            }
        }

        // 税区分。表示設定で「非課税/不課税」がOffになっている場合はプロパティを削除
        if (!in_array("消費税_非課税/不課税", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE'), []))) {
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
            'partnerManagers' => $partnerManagers, // 厳密に言うと書類作成時の担当者情報ではないが保存後、すぐにpdf作成しているのでほぼ書類作成時と同じ内容と考えて差し支えない
            'honorifics' => get_const_item('documents', 'honorific'), // 敬称
            'setting' => [
                config('consts.document_request_alls.DISPLAY_BLOCK') => $this->structuralChange(config('consts.document_request_alls.DISPLAY_BLOCK_LIST')),
                config('consts.document_request_alls.RESERVATION_INFO') => $this->structuralChange(config('consts.document_request_alls.RESERVATION_INFO_LIST')),
                config('consts.document_request_alls.BREAKDOWN_PRICE') => $this->structuralChange(config('consts.document_request_alls.BREAKDOWN_PRICE_LIST'))
            ],
        ];


        //////// 料金情報

        // 数量をまとめた配列を取得
        $reservePriceBreakdown = $this->getReservePriceBreakdown($reserveBundleInvoice->reserve_prices, $partnerManagers);

        $view->with(compact('value', 'formSelects', 'reservePriceBreakdown'));
    }
}
