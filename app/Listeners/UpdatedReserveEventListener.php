<?php

namespace App\Listeners;

use App\Services\ReserveConfirmService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveReceiptService;
use App\Services\ReserveBundleReceiptService;
use App\Events\UpdatedReserveEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdatedReserveEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ReserveConfirmService $reserveConfirmService, ReserveInvoiceService $reserveInvoiceService, ReserveReceiptService $reserveReceiptService, ReserveBundleReceiptService $reserveBundleReceiptService)
    {
        $this->reserveConfirmService = $reserveConfirmService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->reserveReceiptService = $reserveReceiptService;
        $this->reserveBundleReceiptService = $reserveBundleReceiptService;
    }

    /**
     * Handle the event.
     *
     * @param  UpdatedReserveEvent  $event
     * @return void
     */
    public function handle(UpdatedReserveEvent $event)
    {
        $oldReserve = $event->oldReserve;
        $newReserve = $event->newReserve;

        if ($oldReserve->applicantable && $newReserve->applicantable && $oldReserve->applicantable != $newReserve->applicantable) { // 申し込み者情報が変わったら請求書/見積・予約確認書/一括請求書・領収書の申込者情報をクリアする。クリアしないと書類の申込者情報を変更できないため（法人、個人が変わった場合）。また、法人の場合は一括請求の設定とも関わってくるので確実にリセットしておく

            if ($oldReserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS') && $newReserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS') && $oldReserve->applicantable->business_user_id === $newReserve->applicantable->business_user_id) {
                // 同一会社で担当を変更するケースは処理の必要ナシ
            } elseif ($oldReserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_PERSON') && $newReserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_PERSON')) {
                //　個人→個人の変更も処理ナシ
            } else {

                // 申込者が変更された際の請求データ、一括請求データの更新処理
                if (($reserveInvoice = $this->reserveInvoiceService->findByReserveId($newReserve->id))) {
                    $this->reserveInvoiceService->chengedApplicantable(
                        $oldReserve->applicantable,
                        $newReserve->applicantable,
                        $reserveInvoice,
                    );
                }

                // 予約申込書、請求書、領収書の申込者情報クリア。一括請求書＆一括領収の方は法人のみの入力なので特にクリアの必要ナシ
                $this->reserveConfirmService->clearDocumentAddress($newReserve->id);
                $this->reserveInvoiceService->clearDocumentAddress($newReserve->id);
                $this->reserveReceiptService->clearDocumentAddress($newReserve->id);
            }

            if (($reserveInvoice = $this->reserveInvoiceService->findByReserveId($newReserve->id))) {
                // 請求書レコードのapplicant_nameを更新（検索用カラム）
                $applicantName = null;
                if ($newReserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) { // 法人顧客
                    $applicantName = optional($newReserve->applicantable)->name;
                } elseif ($newReserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_PERSON')) { // 個人顧客 *userableを経由してname属性にアクセス
                    $applicantName = optional($newReserve->applicantable->userable)->name;
                }
                $this->reserveInvoiceService->updateFields($reserveInvoice->id, [
                    'applicant_name' => $applicantName,
                ]);
            }
        }
    }
}
