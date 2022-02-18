<?php

namespace App\Listeners;

use App\Services\ReserveConfirmService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveService;
use App\Services\WebReserveService;
use App\Events\CreateItineraryEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// 行程作成時イベント
class CreateItineraryEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ReserveConfirmService $reserveConfirmService, ReserveInvoiceService $reserveInvoiceService, ReserveService $reserveService, WebReserveService $webReserveService)
    {
        $this->reserveConfirmService = $reserveConfirmService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->reserveService = $reserveService;
        $this->webReserveService = $webReserveService;
    }

    /**
     * Handle the event.
     *
     * @param  CreateItineraryEvent  $event
     * @return void
     */
    public function handle(CreateItineraryEvent $event)
    {
        /**
         * 見積or予約確認書が作られていない場合は作成
         */

        // 受付種別で分ける
        if ($event->reserveItinerary->reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_ASP')) { // ASP受付
            $participants = $this->reserveService->getParticipants($event->reserveItinerary->reserve_id, true); //　参加情報

        } elseif ($event->reserveItinerary->reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) { // WEB受付
            $participants = $this->webReserveService->getParticipants($event->reserveItinerary->reserve_id, true); //　参加情報

        } else {
            abort(404);
        }

        switch ($event->reserveItinerary->reserve->application_step) { // 申込段階
            case config('consts.reserves.APPLICATION_STEP_DRAFT'): // 見積
                if (!$this->reserveConfirmService->getQuoteByReserveItineraryId($event->reserveItinerary->id)) { // 見積書データが作成されていない場合は作成
                    $this->reserveConfirmService->createFromReserveItinerary($event->reserveItinerary, $participants);
                }
                break;

            case config('consts.reserves.APPLICATION_STEP_RESERVE'): // 予約

                if (!$this->reserveConfirmService->getReserveConfirmByReserveItineraryId($event->reserveItinerary->id, [], ['id'])) { // 予約確認書が作成されていない場合は作成

                    if (($quote = $this->reserveConfirmService->getQuoteByReserveItineraryId($event->reserveItinerary->id))) { // 見積がある場合は"見積書"をもとに予約確認書を作成
                        $this->reserveConfirmService->createConfirmFromQuote($event->reserveItinerary->agency_id, $quote, $event->reserveItinerary);

                    } else { // 見積書がない場合は"行程をもと"に予約確認書を作成
                        $this->reserveConfirmService->createFromReserveItinerary($event->reserveItinerary, $participants);

                    }
                }

                if (!$this->reserveInvoiceService->findByReserveId($event->reserveItinerary->reserve->id, [], ['id'])) { // 請求書データが作成されていない場合は作成

                    if (($reserveConfirm = $this->reserveConfirmService->getReserveConfirmByReserveItineraryId($event->reserveItinerary->id))) { // 予約確認書がある場合は"予約確認書をもと"に作成
                        $this->reserveInvoiceService->createFromReserveConfirm(
                            $reserveConfirm, 
                            $event->reserveItinerary->reserve, 
                            auth("staff")->user()->id // 最終担当者
                        );

                    } else { // 予約確認書がない場合は"行程データをもと"に作成。（※この分岐は使用されない想定だが一応実装）
                        $this->reserveInvoiceService->createFromReserve(
                            $event->reserveItinerary->reserve, 
                            auth("staff")->user()->id // 最終担当者
                        );
                    }

                }
                break;

            default:
                break;
        }
    }
}
