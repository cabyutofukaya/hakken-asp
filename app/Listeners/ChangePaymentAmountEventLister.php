<?php

namespace App\Listeners;

use App\Traits\PaymentTrait;
use App\Events\ChangePaymentAmountEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\AccountPayableDetailService;
use App\Services\AgencyWithdrawalService;

// 支払管理(詳細)の支払残高、ステータス更新イベント
class ChangePaymentAmountEventLister
{
    use PaymentTrait;
    
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

        // 支払いステータスを取得
        $newStatus = $this->getPaymentStatus($unpaidAmount, $accountPayableDetail->amount_billed, 'account_payable_details');

        // if ($unpaidAmount > 0) { // 支払い残高あり＝未払い
        //     $newStatus = config('consts.account_payable_details.STATUS_UNPAID');
        // } elseif ($unpaidAmount < 0) { // 支払い残高がマイナス＝過払い
        //     $newStatus = config('consts.account_payable_details.STATUS_OVERPAID');
        // } else { // 支払い残高0
        //     $newStatus = $accountPayableDetail->amount_billed ? config('consts.account_payable_details.STATUS_PAID') : config('consts.account_payable_details.STATUS_NONE'); // 請求金額がある場合は支払い済み。それ以外はNoneで初期化
        // }

        if ($currentStatus != $newStatus || $currentUnpaidAmount != $unpaidAmount) { // ステータスか未払い金額が変更されていたら更新
            $this->accountPayableDetailService->updateStatusAndUnpaidBalance($accountPayableDetail->id, $unpaidAmount, $newStatus);
        }
    }
}
