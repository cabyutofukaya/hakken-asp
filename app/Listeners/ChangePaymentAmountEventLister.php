<?php

namespace App\Listeners;

use App\Events\ChangePaymentAmountEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\AccountPayableDetailService;
use App\Services\AgencyWithdrawalService;

class ChangePaymentAmountEventLister
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(AccountPayableDetailService $accountPayableDetailService, AgencyWithdrawalService $agencyWithdrawalService)
    {
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->agencyWithdrawalService = $agencyWithdrawalService;
    }

    /**
     * Handle the event.
     *
     * @param  ChangePaymentAmountEvent  $event
     * @return void
     */
    public function handle(ChangePaymentAmountEvent $event)
    {
        // 行ロックで取得
        $accountPayableDetail = $this->accountPayableDetailService->find($event->accountPayableDetailId, [], [], true);

        $currentStatus = $accountPayableDetail->status; // 現在のステータス
        $currentUnpaidAmount = $accountPayableDetail->unpaid_balance; // 現在の未払金

        // 支払い額合計（行ロックで取得）
        $withdrawalSum = $this->agencyWithdrawalService->getSumAmountByAccountPayableDetailId($accountPayableDetail->id, true);

        $unpaidAmount = $accountPayableDetail->amount_billed - $withdrawalSum; // 未払金額を計算

        if ($accountPayableDetail->amount_billed) { // 請求金額がある場合
            $newStatus = $unpaidAmount > 0 ? config('consts.account_payable_details.STATUS_UNPAID') : config('consts.account_payable_details.STATUS_PAID');
        } else { // 請求金額がない場合のステータスはNone
            $newStatus = config('consts.account_payable_details.STATUS_NONE'); // 変更後のステータス。NONEで初期化
        }

        if ($currentStatus != $newStatus || $currentUnpaidAmount != $unpaidAmount) { // ステータスか未払い金額が変更されていたら更新
            $this->accountPayableDetailService->updateStatusAndUnpaidBalance($accountPayableDetail->id, $unpaidAmount, $newStatus);
        }
    }
}
