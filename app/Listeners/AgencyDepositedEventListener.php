<?php

namespace App\Listeners;

use App\Events\AgencyBundleDepositChangedEvent;
use App\Events\AgencyDepositedEvent;
use App\Services\AgencyBundleDepositService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AgencyDepositedEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(AgencyBundleDepositService $agencyBundleDepositService)
    {
        $this->agencyBundleDepositService = $agencyBundleDepositService;
    }

    /**
     * Handle the event.
     *
     * @param  AgencyDepositedEvent  $event
     * @return void
     */
    public function handle(AgencyDepositedEvent $event)
    {
        if ($event->agencyDeposit->reserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {
            
            // 法人顧客申し込み予約の場合は、agency_bundle_depositsレコードも作成
            $agencyBundleDeposit = $this->agencyBundleDepositService->create(
                array_merge(
                    collect($event->agencyDeposit->toArray())->except([
                        'id',
                        'reserve_id',
                        'reserve_invoice_id',
                        'updated_at',
                        'created_at',
                        'deleted_at',
                        'reserve_invoice'
                        ])->toArray(), // 念の為不要な項目は除去
                    [
                        'reserve_bundle_invoice_id' => $event->agencyDeposit->reserve_invoice->reserve_bundle_invoice_id
                    ] // 一括請求IDをセット
                ),
                false, // 入金IDは生成しない
                $event->checkUpdatedAt // 同時編集チェック
            );

            // 入金額変更処理。入金済・未入金残高計算等
            event(new AgencyBundleDepositChangedEvent($agencyBundleDeposit->reserve_bundle_invoice));

        }
    }
}
