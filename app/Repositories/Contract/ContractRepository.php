<?php
namespace App\Repositories\Contract;

use App\Models\Contract;
use Carbon\Carbon;

class ContractRepository implements ContractRepositoryInterface
{
    /**
    * @param Contract $contract
    */
    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    public function find(int $id) : ?Contract
    {
        return $this->contract->findOrFail($id);
    }

    /**
     * 契約更新
     *
     * 対象条件
     * ・未解約で契約終了日が過ぎている
     * ・退会済みの会社の契約は除外
     * ・更新済契約は除外
     * 
     *  @return int 処理件数
     */
    public function renewal() : int
    {
        $cnt = 0; // 処理件数

        Contract::has('agency')->where('renewal', false)->whereNull('cancellation_at')->where('end_at', '<', Carbon::now())->chunk(100, function ($contracts) use (&$cnt) { // 処理件数が増え過ぎないように100件ずつ処理
            foreach ($contracts as $contract) {
                // 現状の契約の契約終了日翌日を起点に更新後の契約日と実際の契約日の日付を取得

                if (preg_match('/(\d{4})\-(\d{1,2})\-(\d{1,2})/', $contract->end_at->addDay(1)->toDateString(), $m)) {
                    // YYYY-MM-DD形式の日付に時刻（00:00:00）を補完
                    $startAt = Carbon::create($m[1], $m[2], $m[3], 0, 0, 0);

                    // 契約開始日時から契約終了日時を求める
                    $endAt = $startAt->copy()->addMonths($contract->contract_plan->period)->subDay()->format('Y-m-d 23:59:59');

                    // 新しい契約レコードを作成
                    $old = $this->find($contract->id);
                    $new = $old->replicate();
                    $new->parent_id = $old->id;
                    $new->start_at = $startAt;
                    $new->end_at = $endAt;
                    $new->save();

                    // 現在の契約は更新済フラグをOnにして保存
                    $old->renewal = true;
                    $old->save();

                    $cnt++;

                }

            }
        });

        return $cnt;
    }
}
