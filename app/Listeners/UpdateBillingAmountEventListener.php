<?php

namespace App\Listeners;

use App\Events\UpdateBillingAmountEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\ReserveInvoiceService;
use App\Traits\BusinessFormTrait;

class UpdateBillingAmountEventListener
{
    use BusinessFormTrait;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ReserveInvoiceService $reserveInvoiceService)
    {
        $this->reserveInvoiceService = $reserveInvoiceService;
    }

    /**
     * Handle the event.
     *
     * @param  UpdateBillingAmountEvent  $event
     * @return void
     */
    public function handle(UpdateBillingAmountEvent $event)
    {
        /**
         * [請求金額の更新(予約状態が対象)]
         *
         * 行程データを元に各種価格データを取得
         * ↓
         * 請求書に設定された参加者情報を元に請求額を計算
         * ↓
         * 請求書の金額情報を更新
         * ↓
         * 一括請求関連の更新処理
         */

        $reserveInvoice = $this->reserveInvoiceService->findByReserveItineraryId($event->reserveItinerary->id, [], ['amount_total','participant_ids','option_prices','airticket_prices','hotel_prices'], false);

        if ($reserveInvoice) { // 請求書データがある場合のみ処理。見積もり段階の場合は処理ナシ
            // 行程データからオプション価格情報、航空券価格情報、ホテル価格情報を取得
            list($optionPrices, $airticketPrices, $hotelPrices, $disp1, $disp2) = $this->getPriceAndHotelInfo($event->reserveItinerary, $event->reserveItinerary->reserve->is_canceled, true);

            // 書類に設定された参加者情報から合計金額を計算
            if ($event->reserveItinerary->reserve->is_canceled) {
                $amountTotal = get_cancel_charge_total($reserveInvoice->participant_ids, $optionPrices, $airticketPrices, $hotelPrices);
            } else {
                $amountTotal = get_price_total($reserveInvoice->participant_ids, $optionPrices, $airticketPrices, $hotelPrices);
            }

            // 合計金額が変わっていれば請求書、再計算処理を実行
            if ($amountTotal !== $reserveInvoice->amount_total) {
                $reserveInvoice = $this->reserveInvoiceService->updateOrCreate(['reserve_itinerary_id' => $event->reserveItinerary->id], ['amount_total' => $amountTotal]);

                // 入金額の再計算＆更新した最新の請求情報を取得
                $newReserveInvoice = $this->reserveInvoiceService->updateDepositAmount($reserveInvoice);
    
                // 一括請求関連処理（作成、リレーション設定等）
                $this->reserveInvoiceService->reserveBundleInvoiceRefresh($reserveInvoice->reserve_bundle_invoice_id, $newReserveInvoice);
            }
        }
    }
}
