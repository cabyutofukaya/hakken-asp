<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\ChangePaymentAmountEvent' => [
            'App\Listeners\ChangePaymentAmountEventLister',
        ],
        // 予約レコードのステータスを更新
        'App\Events\ReserveUpdateStatusEvent' => [
            'App\Listeners\ReserveUpdateStatusEventListener',
        ],
        // 予約レコードの人数を更新
        'App\Events\ReserveChangeHeadcountEvent' => [
            'App\Listeners\ReserveChangeHeadcountEventListener',
        ],
        // 予約代金の合計金額を更新
        'App\Events\ReserveChangeSumGrossEvent' => [
            'App\Listeners\ReserveChangeSumGrossEventListener',
        ],
        // 代表者名変更
        'App\Events\ReserveChangeRepresentativeEvent' => [
            'App\Listeners\ReserveChangeRepresentativeEventListener',
        ],
        // 予約時の初期化処理等
        'App\Events\ReserveEvent' => [
            'App\Listeners\ReserveEventListener',
        ],
        // 予約更新後処理。申込者区分が変更された場合の処理など
        'App\Events\UpdatedReserveEvent' => [
            'App\Listeners\UpdatedReserveEventListener',
        ],
        // 行程作成時処理等
        'App\Events\CreateItineraryEvent' => [
            'App\Listeners\CreateItineraryEventListener',
        ],
        // 通常請求に対する入金処理後イベント
        'App\Events\AgencyDepositedEvent' => [
            'App\Listeners\AgencyDepositedEventListener',
        ],
        // 通常請求に対する入金額変更イベント
        'App\Events\AgencyDepositChangedEvent' => [
            'App\Listeners\AgencyDepositChangedEventListener',
        ],
        // 一括請求に対する入金額変更イベント
        'App\Events\AgencyBundleDepositChangedEvent' => [
            'App\Listeners\AgencyBundleDepositChangedEventListener',
        ],
        // 請求書作成時イベント
        'App\Events\ReserveInvoiceCreatedEvent' => [
            'App\Listeners\ReserveInvoiceCreatedEventListener',
        ],
        // プラン変更イベント(HAKKEN)
        'App\Events\WebModelcourseChangeEvent' => [
            'App\Listeners\WebModelcourseChangeEventListener',
        ],
        // メッセージ既読イベント(HAKKEN)
        'App\Events\WebMessageReadEvent' => [
            'App\Listeners\WebMessageReadEventListener',
        ],
        // メッセージ送信イベント(HAKKEN)
        'App\Events\WebMessageSendEvent' => [
            'App\Listeners\WebMessageSendEventListener',
        ],
        // オンライン相談変更リクエストイベント
        'App\Events\AsyncWebOnlineChangeScheduleEvent' => [
            'App\Listeners\AsyncWebOnlineChangeScheduleEventListener',
        ],
        // オンライン相談承諾イベント
        'App\Events\AsyncWebOnlineConsentEvent' => [
            'App\Listeners\AsyncWebOnlineConsentEventListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
