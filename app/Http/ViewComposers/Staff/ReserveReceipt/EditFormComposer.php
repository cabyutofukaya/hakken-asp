<?php
namespace App\Http\ViewComposers\Staff\ReserveReceipt;

use App\Services\DocumentCommonService;
use App\Services\DocumentReceiptService;
use App\Services\ReserveReceiptService;
use App\Traits\BusinessFormTrait;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 領収書作成ページに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use JsConstsTrait, BusinessFormTrait;

    public function __construct(
        DocumentCommonService $documentCommonService,
        DocumentReceiptService $documentReceiptService,
        ReserveReceiptService $reserveReceiptService
    ) {
        $this->documentCommonService = $documentCommonService;
        $this->documentReceiptService = $documentReceiptService;
        $this->reserveReceiptService = $reserveReceiptService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得

        $reserveInvoice = Arr::get($data, 'reserveInvoice');
        $reserveReceipt = Arr::get($data, 'reserveReceipt', null);
        $reserve = Arr::get($data, 'reserve');

        //////////////////////////////////

        $my = auth("staff")->user();
        $agencyId = $my->agency_id;
        $agencyAccount = $my->agency->account;

        // 受付種別
        $reception = config('consts.const.RECEPTION_TYPE_ASP');

        // 催行済みか否か
        $isDeparted = $reserve->is_departed;

        // キャンセルか否か
        $isCanceled = $reserve->is_canceled;

        $defaultValue = [];

        $reserveUpdatedAt = $reserve->updated_at->format('Y-m-d H:i:s'); // 同時編集の判定に使用

        if ($reserveReceipt) { // 招集書保存データあり

            $documentReceiptId = $reserveReceipt->document_setting['id'] ?? '';
            $documentCommonId = $reserveReceipt->document_common_id ?? '';

            // 共通設定
            $documentCommonSetting = $reserveReceipt->document_common_setting;

            $documentSetting = $reserveReceipt->document_setting;

            /////////// 入力初期値をセット ///////////
            $userReceiptNumber = $reserveReceipt->user_receipt_number;
            
            // 発行日
            $issueDate = $reserveReceipt->issue_date ?? date('Y/m/d');

            // 領収金額
            $receiptAmount = $reserveReceipt->receipt_amount ?? 0;

            // 担当
            $manager = $reserveReceipt->manager;
            
            // 申込者情報
            $documentAddress = $reserveReceipt->document_address ?? $this->getDocumentAddress($reserveInvoice->reserve->applicantable); // 申込者情報が設定されていない場合は予約情報から取得（書類作成後、申込者を変更した場合、本カラムはリセットされている）。

            // 法人顧客ID
            $businessUserId = $reserveReceipt->business_user_id ?? $reserveInvoice->reserve->applicantable->business_user_id;

            $status = $reserveReceipt->status;

            ///////////////// テンプレートセレクトメニュー /////////////////

            // 編集時は論理削除を考慮してselectメニューを取得
            $documentReceipts = ['' => '---'] + $this->documentReceiptService->getIdNameSelectSafeValues($agencyId, [$documentReceiptId]);
        } else { // 新規

            $documentReceipt = $this->documentReceiptService->getDefault($agencyId);
            $documentReceiptId = $documentReceipt->id;
            $documentCommonId = $documentReceipt->document_common_id ?? "";
            
            // 共通設定
            $documentCommonSetting = $documentReceipt ? $documentReceipt->document_common->toArray() : [];

            // 書類設定。$documentReceiptが未設定なれば空配列で初期化
            $documentSetting = $documentReceipt ? $documentReceipt->toArray() : [];
            
            /////////// 入力初期値をセット ///////////

            // システム的にあまり意味はないが、一応領収書番号を発行
            $receiptNumber = $this->reserveReceiptService->createReceiptNumber($agencyId);
            $userReceiptNumber = $receiptNumber;
            $defaultValue['receipt_number'] = $receiptNumber; // 新規作成時のみセット

            // 発行日
            $issueDate = date('Y/m/d');

            // 領収金額
            $receiptAmount = $reserveInvoice->sum_deposit ?? 0;

            // 担当
            $manager = $reserveInvoice->manager;

            // 申込者情報
            $documentAddress = $reserveInvoice->document_address;

            $businessUserId = $reserveInvoice->business_user_id; // 法人顧客ID

            $status = config('consts.reserve_receipts.STATUS_DEFAULT');

            ///////////////// テンプレートセレクトメニュー /////////////////

            // 新規作成時は削除済みテンプレートは取得しない
            $documentReceipts = ['' => '---'] + $this->documentReceiptService->getIdNameSelect($agencyId);
        }

        //　上限金額=請求金額
        $maximumAmount = $reserveInvoice->amount_total ?? 0;

        // 各種デフォルト
        $defaultValue = array_merge($defaultValue, [
            'id' => $reserveReceipt ? $reserveReceipt->id :null,
            'business_user_id' => $businessUserId, // 法人顧客ID
            'document_receipt_id' => $documentReceiptId, // 書類設定ID
            'document_common_id' => $documentCommonId, // 共通書類設定ID
            'user_receipt_number' => $userReceiptNumber, // 見積番号
            'issue_date' => $issueDate, // 発行日
            'receipt_amount' => $receiptAmount,
            'document_address' => $documentAddress, // 宛名情報
            'manager' => $manager, // 担当
            'status' => $status, // 書類ステータス
            'reserve' => [
                'updated_at' => $reserveUpdatedAt,
            ],
        ]);

        $formSelects = [
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelectSafeValues($agencyId, [$documentCommonId]), // 宛名/自社情報共通設定selectメニュー
            'documentReceipts' => $documentReceipts,
            'honorifics' => get_const_item('documents', 'honorific'), // 敬称
            'statuses' => get_const_item('reserve_receipts', 'status'), // ステータス
        ];

        $consts = $this->getConstDatas();
        $consts['departedIndexUrl'] = route('staff.estimates.departed.index', $agencyAccount); // 催行済URL

        // 各種URL。ASP or WEB申し込みで出し分け
        $consts['reserveIndexUrl'] = '';
        $consts['reserveUrl'] = '';

        if ($reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $consts['reserveIndexUrl'] = route('staff.asp.estimates.reserve.index', [$agencyAccount]);
            $consts['reserveUrl'] = route('staff.asp.estimates.reserve.show', [$agencyAccount, $reserve->control_number ?? '']);

        } elseif ($reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $consts['reserveIndexUrl'] = route('staff.web.estimates.reserve.index', [$agencyAccount]);
            $consts['reserveUrl'] = route('staff.web.estimates.reserve.show', [$agencyAccount, $reserve->control_number ?? '']);

        }
        
        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('maximumAmount', 'defaultValue', 'formSelects', 'consts', 'documentCommonSetting', 'documentSetting', 'jsVars', 'reception', 'isDeparted', 'isCanceled'));
    }
}
