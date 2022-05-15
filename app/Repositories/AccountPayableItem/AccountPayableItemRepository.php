<?php
namespace App\Repositories\AccountPayableItem;

use App\Models\AccountPayableItem;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountPayableItemRepository implements AccountPayableItemRepositoryInterface
{
    /**
    * @param object $accountPayableItem
    */
    public function __construct(AccountPayableItem $accountPayableItem)
    {
        $this->accountPayableItem = $accountPayableItem;
    }

    /**
     * 当該レコードを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     * @param int $isLock 行ロックして取得する場合はtrue
     */
    public function find(int $id, array $with = [], array $select = [], bool $isLock = false): ?AccountPayableItem
    {
        $query = $this->accountPayableItem;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $isLock ? $query->lockForUpdate()->find($id) : $query->find($id);
    }

    public function update(int $id, array $data): AccountPayableItem
    {
        $accountPayableItem = $this->accountPayableItem->find($id);
        $accountPayableItem->fill($data)->save();
        return $accountPayableItem;
    }

    /**
     * フィールド更新
     */
    public function updateField(int $id, array $data): AccountPayableItem
    {
        $this->accountPayableItem->where('id', $id)->update($data);
        return $this->accountPayableItem->find($id);
    }

    /**
     * 当該行程IDのNet・未払金額を更新
     *
     * @param int $reserveItineraryId 行程ID
     */
    public function refreshAmountByReserveItineraryId(int $reserveItineraryId) : bool
    {
        // payable_numberの生成に使うカラム群
        $payableNumberColumn = "a.agency_id, a.reserve_id, a.reserve_itinerary_id, a.supplier_id, a.subject, a.item_id";

        // ステータス値
        $statusUnpaid = config("consts.account_payable_items.STATUS_UNPAID");
        $statusOverpaid = config("consts.account_payable_items.STATUS_OVERPAID");
        $statusPaid = config("consts.account_payable_items.STATUS_PAID");
        $statusNone = config("consts.account_payable_items.STATUS_NONE");

        // 当該行程に対し、payable_numberカラムが存在していれば(金額カラムを)更新、なければレコードを登録。削除済み行(deleted_at)は集計には含めない
        // 支払日は支払管理ページにて編集可能なのでここでは更新はせず、新規登録のみ行う
        // statusのcase文はPaymentTrait@getPaymentStatusと同じロジック
        $sql = "
        INSERT INTO account_payable_items(
            payable_number,
            agency_id,
            reserve_id,
            reserve_itinerary_id,
            supplier_id,
            supplier_name,
            item_id,
            item_code,
            item_name,
            subject,
            amount_billed,
            unpaid_balance,
            payment_date,
            status,
            updated_at,
            created_at
        )
        SELECT
            payable_number,
            agency_id,
            reserve_id,
            reserve_itinerary_id,
            supplier_id,
            supplier_name,
            item_id,
            item_code,
            item_name,
            subject,
            amount_billed,
            unpaid_balance,
            payment_date,
            status,
            NOW(),
            NOW()
        FROM
            (
                SELECT
                    MD5(CONCAT({$payableNumberColumn})) AS payable_number,
                    a.agency_id,
                    a.reserve_id,
                    a.reserve_itinerary_id,
                    a.supplier_id,
                    b.name AS supplier_name,
                    a.item_id,
                    a.item_code,
                    a.item_name,
                    a.subject,
                    sum(CASE WHEN a.deleted_at IS NULL THEN a.amount_billed ELSE 0 END) AS amount_billed,
                    sum(CASE WHEN a.deleted_at IS NULL THEN a.unpaid_balance ELSE 0 END) AS unpaid_balance,
                    c.payment_date,
                    CASE
                        WHEN sum(CASE WHEN a.deleted_at IS NULL THEN a.unpaid_balance ELSE 0 END) > 0 THEN {$statusUnpaid}
                        WHEN sum(CASE WHEN a.deleted_at IS NULL THEN a.unpaid_balance ELSE 0 END) < 0 THEN {$statusOverpaid}
                        WHEN sum(CASE WHEN a.deleted_at IS NULL THEN a.unpaid_balance ELSE 0 END) = 0 AND sum(CASE WHEN a.deleted_at IS NULL THEN a.amount_billed ELSE 0 END) > 0 THEN {$statusPaid}
                        ELSE {$statusNone}
                    END AS status
                FROM
                    account_payable_details AS a
                    LEFT JOIN
                        suppliers AS b
                    ON  a.supplier_id = b.id
                    LEFT JOIN
                        supplier_payment_dates AS c
                    ON  a.reserve_id = c.reserve_id AND a.supplier_id = c.supplier_id
                WHERE
                    reserve_itinerary_id = {$reserveItineraryId}
                GROUP BY
                    reserve_itinerary_id,
                    supplier_id,
                    item_code,
                    saleable_type
            ) t
        ON DUPLICATE KEY UPDATE
            amount_billed = t.amount_billed,
            unpaid_balance = t.unpaid_balance,
            supplier_name = t.supplier_name,
            status = t.status,
            updated_at = NOW()
        ";

        \DB::statement($sql);

        return true;
    }

    /**
     * ページネーション で取得
     *
     * @param ?string $applicationStep 申し込み段階。全レコード対象の場合はnull
     * @var $limit
     * @param bool $exZero 仕入額・未払い額が0円のレコードを取得しない場合はtrue
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, ?string $applicationStep, array $with, array $select, bool $exZero = true) : LengthAwarePaginator
    {
        $query = $applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE') ? $this->accountPayableItem->decided() : $this->accountPayableItem; // スコープを設定
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目
                // 子テーブルとなるagency_withdrawal_item_historiesレコードのカスタム項目が対象
                $query = $query->whereHas('agency_withdrawal_item_histories.v_agency_withdrawal_item_history_custom_values', function ($q) use ($key, $val) {
                    $q->where('key', $key)->where('val', 'like', "%$val%");
                });

            // 「予約ID」と「行程ID」は必須パラメータにつき曖昧検索はしない
            } elseif ($key === 'reserve_id') { // 予約ID
                $query = $query->where('reserve_id', $val);
            } elseif ($key === 'reserve_itinerary_id') { // 行程ID
                $query = $query->where('reserve_itinerary_id', $val);
            ///////////////
            } elseif ($key === 'reserve_number') { // 予約番号
                $query = $query->whereHas('reserve', function ($q) use ($val) {
                    $q->where('control_number', 'like', "%$val%");
                });
            } elseif ($key === 'last_manager_id' || $key === 'status') { // 自社担当、ステータス
                $query = $query->where($key, $val);
            } elseif ($key=== 'payment_date_from') { // 支払予定日(From)
                $query = $query->where('payment_date', '>=', $val);
            } elseif ($key=== 'payment_date_to') { // 支払予定日(To)
                $query = $query->where('payment_date', '<=', $val);
            } else { // 仕入先
                $query = $query->where($key, 'like', "%$val%");
            }
        }

        if ($exZero) { // 請求額が0円だと、出金履歴が有っても非表示になるので注意。具合悪いようならこのフラグはなくす
            $query = $query->where(function ($q) {
                $q->where('amount_billed', "<>", 0)
                    ->orWhere('unpaid_balance', "<>", 0);
            })->where('status', '<>', config('consts.account_payable_items.STATUS_NONE'));
        }

        return $query->where('account_payable_items.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }
}
