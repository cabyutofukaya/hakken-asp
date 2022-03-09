<?php
namespace App\Http\ViewComposers\Staff\ReserveBundleReceipt;

use App\Services\DocumentCommonService;
use App\Services\DocumentReceiptService;
use App\Services\ReserveBundleReceiptService;
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
        ReserveBundleReceiptService $reserveBundleReceiptService
    ) {
        $this->documentCommonService = $documentCommonService;
        $this->documentReceiptService = $documentReceiptService;
        $this->reserveBundleReceiptService = $reserveBundleReceiptService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得

        $bundleId = Arr::get($data, 'reserveBundleInvoiceHashId');
        $reserveBundleInvoice = Arr::get($data, 'reserveBundleInvoice');
        $reserveBundleReceipt = Arr::get($data, 'reserveBundleReceipt', null);

        //////////////////////////////////

        $my = auth("staff")->user();
        $agencyId = $my->agency_id;
        $agencyAccount = $my->agency->account;

        $defaultValue = [];

        if ($reserveBundleReceipt) { // 招集書保存データあり

            $documentReceiptId = $reserveBundleReceipt->document_setting['id'] ?? '';
            $documentCommonId = $reserveBundleReceipt->document_common_id ?? '';

            // 共通設定
            $documentCommonSetting = $reserveBundleReceipt->document_common_setting;

            $documentSetting = $reserveBundleReceipt->document_setting;

            $updatedAt = $reserveBundleReceipt->updated_at->format('Y-m-d H:i:s'); // 同時編集の判定に使用

            /////////// 入力初期値をセット ///////////
            $userReceiptNumber = $reserveBundleReceipt->user_receipt_number;
            
            // 発行日
            $issueDate = $reserveBundleReceipt->issue_date ?? date('Y/m/d');

            // 領収金額
            $receiptAmount = $reserveBundleReceipt->receipt_amount ?? 0;

            // 担当
            $manager = $reserveBundleReceipt->manager;
            
            // 申込者情報
            $documentAddress = $reserveBundleReceipt->document_address ?? $this->getDocumentAddress($reserveBundleInvoice->reserve->applicantable); // 申込者情報が設定されていない場合は念のため予約情報から取得

            // // 法人顧客ID
            // $businessUserId = $reserveBundleReceipt->business_user_id ?? $reserveBundleInvoice->reserve->applicantable->business_user_id;

            $status = $reserveBundleReceipt->status;

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
            
            $updatedAt = null;

            /////////// 入力初期値をセット ///////////

            // システム的にあまり意味はないが一応領収書番号を発行
            $receiptNumber = $this->reserveBundleReceiptService->createReceiptNumber($agencyId);
            $userReceiptNumber = $receiptNumber;
            $defaultValue['receipt_number'] = $receiptNumber; // 新規作成時のみセット

            // 発行日
            $issueDate = date('Y/m/d');

            // 領収金額
            $receiptAmount = $reserveBundleInvoice->sum_deposit ?? 0;

            // 担当
            $manager = $reserveBundleInvoice->manager;

            // 申込者情報
            $documentAddress = $reserveBundleInvoice->document_address;

            // $businessUserId = $reserveBundleInvoice->business_user_id; // 法人顧客ID

            $status = config('consts.reserve_receipts.STATUS_DEFAULT');

            ///////////////// テンプレートセレクトメニュー /////////////////

            // 新規作成時は削除済みテンプレートは取得しない
            $documentReceipts = ['' => '---'] + $this->documentReceiptService->getIdNameSelect($agencyId);
        }

        //　上限金額=請求金額
        $maximumAmount = $reserveBundleInvoice->amount_total ?? 0;

        // 各種デフォルト
        $defaultValue = array_merge($defaultValue, [
            // 'business_user_id' => $businessUserId, // 法人顧客ID
            'id' => $reserveBundleReceipt ? $reserveBundleReceipt->id : null,
            'document_receipt_id' => $documentReceiptId, // 書類設定ID
            'document_common_id' => $documentCommonId, // 共通書類設定ID
            'user_receipt_number' => $userReceiptNumber, // 見積番号
            'issue_date' => $issueDate, // 発行日
            'receipt_amount' => $receiptAmount,
            'document_address' => $documentAddress, // 宛名情報
            'manager' => $manager, // 担当
            'updated_at' => $updatedAt,
            'status' => $status, // 書類ステータス
        ]);

        $formSelects = [
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelectSafeValues($agencyId, [$documentCommonId]), // 宛名/自社情報共通設定selectメニュー
            'documentReceipts' => $documentReceipts,
            'honorifics' => get_const_item('documents', 'honorific'), // 敬称
            'statuses' => get_const_item('reserve_bundle_receipts', 'status'), // ステータス
        ];

        $consts = $this->getConstDatas();

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('bundleId', 'maximumAmount', 'defaultValue', 'formSelects', 'consts', 'documentCommonSetting', 'documentSetting', 'jsVars'));
    }
}
