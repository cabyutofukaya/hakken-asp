<?php

namespace App\Listeners;

use App\Events\UpdateBillingAmountEvent;
use App\Services\ReserveConfirmService;
use App\Services\ReserveInvoiceService;
use App\Traits\BusinessFormTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateBillingAmountEventListener
{
    use BusinessFormTrait;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ReserveConfirmService $reserveConfirmService, ReserveInvoiceService $reserveInvoiceService)
    {
        $this->reserveConfirmService = $reserveConfirmService;
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

        $reserveItinerary = $event->reserve->enabled_reserve_itinerary->id ? $event->reserve->enabled_reserve_itinerary : null; // 有効な行程が設定されていれば行程情報をセット

        $reserveInvoice = null;
        if ($reserveItinerary) {
            $reserveInvoice = $this->reserveInvoiceService->findByReserveItineraryId($reserveItinerary->id, [], ['amount_total','participant_ids','option_prices','airticket_prices','hotel_prices'], false);
        }

        if ($reserveInvoice) { // 請求書データがある場合のみ処理
            // 行程データからオプション価格情報、航空券価格情報、ホテル価格情報等を取得
            list($optionPrices, $airticketPrices, $hotelPrices, $hotelInfo, $hotelContacts) = $this->getPriceAndHotelInfo($reserveItinerary, false);

            // // 書類に設定された参加者情報から合計金額を計算
            // $amountTotal = get_price_total($reserveInvoice->participant_ids, $optionPrices, $airticketPrices, $hotelPrices);

            // 合計金額が変わっていれば請求書、再計算処理を実行
            // if ($amountTotal !== $reserveInvoice->amount_total) {

            // 料金に関する設定が変わっていたら再計算(hotel_infoとhotel_contactsは念の為判定材料に)
            if ($reserveInvoice->option_prices != $optionPrices ||
                $reserveInvoice->airticket_prices != $airticketPrices ||
                $reserveInvoice->hotel_prices != $hotelPrices ||
                $reserveInvoice->hotel_info != $hotelInfo ||
                $reserveInvoice->hotel_contacts != $hotelContacts
            ) {
                // 書類に設定された参加者情報から合計金額を計算
                $amountTotal = get_price_total($reserveInvoice->participant_ids, $optionPrices, $airticketPrices, $hotelPrices);

                $ri = $this->reserveInvoiceService->updateOrCreate(['reserve_itinerary_id' => $reserveItinerary->id], [
                    'option_prices' => $optionPrices,
                    'airticket_prices' => $airticketPrices,
                    'hotel_prices' => $hotelPrices,
                    'hotel_info' => $hotelInfo,
                    'hotel_contacts' => $hotelContacts,
                    // 合計金額
                    'amount_total' => $amountTotal
                ]);

                // 入金額の再計算＆更新した最新の請求情報を取得
                $newReserveInvoice = $this->reserveInvoiceService->updateDepositAmount($ri);
    
                // 一括請求関連処理（作成、リレーション設定等）
                $this->reserveInvoiceService->reserveBundleInvoiceRefresh($ri->reserve_bundle_invoice_id, $newReserveInvoice);
            }
        }

        /**
         * 見積・予約確認書の金額データを更新
         *
         * 当該行程に紐づく見積・予約確認データを取得
         * ↓
         * 見積・予約確認書に設定された参加者情報を元に請求額を計算
         * ↓
         * 見積・予約確認書の金額情報を更新
         *
         */
        $reserveConfirms = null;
        if ($reserveItinerary) {
            $reserveConfirms = $this->reserveConfirmService->getByReserveItineraryId($reserveItinerary->id, [], ['id','amount_total','participant_ids','option_prices','airticket_prices','hotel_prices'], false);
        }
        if ($reserveConfirms) {
            foreach ($reserveConfirms as $reserveConfirm) {
                // 行程データからオプション価格情報、航空券価格情報、ホテル価格情報を取得
                list($optionPrices, $airticketPrices, $hotelPrices, $hotelInfo, $hotelContacts) = $this->getPriceAndHotelInfo($reserveItinerary, false);
    
                // // 書類に設定された参加者情報から合計金額を計算
                // $amountTotal = get_price_total($reserveConfirm->participant_ids, $optionPrices, $airticketPrices, $hotelPrices);

                // // 合計金額が変わっていれamount_totalカラムを更新
                // if ($amountTotal !== $reserveConfirm->amount_total) {

                // 料金に関する設定が変わっていたら再計算(hotel_infoとhotel_contactsは念の為判定材料に)
                if ($reserveConfirm->option_prices != $optionPrices ||
                    $reserveConfirm->airticket_prices != $airticketPrices ||
                    $reserveConfirm->hotel_prices != $hotelPrices ||
                    $reserveConfirm->hotel_info != $hotelInfo ||
                    $reserveConfirm->hotel_contacts != $hotelContacts
                ) {
                    // 書類に設定された参加者情報から合計金額を計算
                    $amountTotal = get_price_total($reserveConfirm->participant_ids, $optionPrices, $airticketPrices, $hotelPrices);

                    // $this->reserveConfirmService->updateAmountTotal($reserveConfirm->id, $amountTotal);

                    $this->reserveConfirmService->updateOrCreate(['id' => $reserveConfirm->id], [
                        'option_prices' => $optionPrices,
                        'airticket_prices' => $airticketPrices,
                        'hotel_prices' => $hotelPrices,
                        'hotel_info' => $hotelInfo,
                        'hotel_contacts' => $hotelContacts,
                        // 合計金額
                        'amount_total' => $amountTotal
                    ]);
                }
            }
        }
    }
}
