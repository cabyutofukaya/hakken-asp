<?php

namespace App\Listeners;

use App\Events\ChangePaymentReserveAmountEvent;
use App\Services\AccountPayableReserveService;
use App\Services\AgencyWithdrawalService;
use App\Traits\PaymentTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// 支払管理(予約)の支払残高、ステータス更新イベント
class ChangePaymentReserveAmountEventLister
{
    use PaymentTrait;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(AccountPayableReserveService $accountPayableReserveService, AgencyWithdrawalService $agencyWithdrawalService)
    {
        $this->accountPayableReserveService = $accountPayableReserveService;
        $this->agencyWithdrawalService = $agencyWithdrawalService;
    }

    /**
     * Handle the event.
     *
     * @param  ChangePaymentReserveAmountEvent  $event
     * @return void
     */
    public function handle(ChangePaymentReserveAmountEvent $event)
    {
        // 行ロックで取得
        $accountPayableReserve = $this->accountPayableReserveService->findByReserveId($event->reserveId, [], [], true);

        $currentStatus = $accountPayableReserve->status; // 現在のステータス
        $currentUnpaidAmount = $accountPayableReserve->unpaid_balance; // 現在の未払金

        // 支払い額合計（行ロックで取得）
        $withdrawalSum = $this->agencyWithdrawalService->getSumAmountByReserveId($event->reserveId, true);

        $unpaidAmount = $accountPayableReserve->amount_billed - $withdrawalSum; // 未払金額を計算

        // 支払いステータスを取得
        $newStatus = $this->getPaymentStatus($unpaidAmount, $accountPayableReserve->amount_billed, 'account_payable_reserves');

        // if ($unpaidAmount > 0) { // 支払い残高あり＝未払い
        //     $newStatus = config('consts.account_payable_reserves.STATUS_UNPAID');
        // } elseif ($unpaidAmount < 0) { // 支払い残高がマイナス＝過払い
        //     $newStatus = config('consts.account_payable_reserves.STATUS_OVERPAID');
        // } else { // 支払い残高0
        //     $newStatus = $accountPayableReserve->amount_billed ? config('consts.account_payable_reserves.STATUS_PAID') : config('consts.account_payable_reserves.STATUS_NONE'); // 請求金額がある場合は支払い済み。それ以外はNoneで初期化
        // }

        if ($currentStatus != $newStatus || $currentUnpaidAmount != $unpaidAmount) { // ステータスか未払い金額が変更されていたら更新
            $this->accountPayableReserveService->updateStatusAndUnpaidBalance($accountPayableReserve->id, $unpaidAmount, $newStatus);
        }
    }
}
