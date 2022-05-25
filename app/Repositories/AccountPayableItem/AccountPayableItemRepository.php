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

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]) : ?AccountPayableItem
    {
        $query = $this->accountPayableItem;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->first();
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
    public function refreshAmountByReserveItineraryId(?int $reserveItineraryId) : bool
    {
        if (!$reserveItineraryId) return true; // 有効行程がない場合は処理ナシ
        
        $itemPayableNumberColumn = implode(
            ",",
            array_map(function ($colName) {
                return "a.{$colName},'" . config("consts.account_payable_items.ITEM_PAYABLE_NUMBER_DELIMITER") . "'"; // CONCAT関数実行のためテーブル名のエイリアスをつける(a)。また、各値の桁数が決まっていないので一意性を保証するために各値をハイフンで区切る（作成する値は例えば次のような文字列　57-17-airplane-3-）。
            }, config("consts.account_payable_items.ITEM_PAYABLE_NUMBER_COLUMNS")) // item_payable_number生成に使うカラム群
        );

        // ステータス値
        $statusUnpaid = config("consts.account_payable_items.STATUS_UNPAID");
        $statusOverpaid = config("consts.account_payable_items.STATUS_OVERPAID");
        $statusPaid = config("consts.account_payable_items.STATUS_PAID");
        $statusNone = config("consts.account_payable_items.STATUS_NONE");

        // 当該行程に対し、item_payable_numberカラムが存在していれば(金額カラムを)更新、なければレコードを登録。削除済み行(deleted_at)は集計には含めない
        // 支払日は支払管理ページにて編集可能なのでここでは更新はせず、新規登録のみ行う
        $sql = "
        INSERT INTO account_payable_items(
            item_payable_number,
            agency_id,
            reserve_id,
            reserve_itinerary_id,
            supplier_id,
            supplier_name,
            item_id,
            item_code,
            item_name,
            subject,
            total_purchase_amount,
            total_amount_paid,
            total_amount_accrued,
            total_overpayment,
            payment_date,
            status,
            updated_at,
            created_at
        )
        SELECT
            item_payable_number,
            agency_id,
            reserve_id,
            reserve_itinerary_id,
            supplier_id,
            supplier_name,
            item_id,
            item_code,
            item_name,
            subject,
            total_purchase_amount,
            total_amount_paid,
            total_amount_accrued,
            total_overpayment,
            payment_date,
            status,
            NOW(),
            NOW()
        FROM
            (
                SELECT
                    MD5(CONCAT({$itemPayableNumberColumn})) AS item_payable_number,
                    a.agency_id,
                    a.reserve_id,
                    a.reserve_itinerary_id,
                    a.supplier_id,
                    b.name AS supplier_name,
                    a.item_id,
                    a.item_code,
                    a.item_name,
                    a.subject,
                    sum(a.amount_billed) AS total_purchase_amount,
                    sum(a.amount_payment) AS total_amount_paid,
                    sum(if(a.unpaid_balance > 0, a.unpaid_balance, 0)) AS total_amount_accrued,
                    sum(if(a.unpaid_balance < 0, a.unpaid_balance, 0)) AS total_overpayment,
                    c.payment_date,
                    CASE
                        WHEN sum(if(a.unpaid_balance > 0, a.unpaid_balance, 0)) > 0 THEN {$statusUnpaid}
                        WHEN sum(a.amount_payment) > sum(a.amount_billed) THEN {$statusOverpaid}
                        WHEN sum(a.amount_billed) > 0 AND sum(a.amount_billed) = sum(a.amount_payment) THEN {$statusPaid}
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
                    reserve_itinerary_id = {$reserveItineraryId} AND a.deleted_at IS NULL
                GROUP BY
                    reserve_itinerary_id,
                    supplier_id,
                    item_code,
                    saleable_type
            ) t
        ON DUPLICATE KEY UPDATE
            total_purchase_amount = t.total_purchase_amount,
            total_amount_paid = t.total_amount_paid,
            total_amount_accrued = t.total_amount_accrued,
            total_overpayment = t.total_overpayment,
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

        if ($exZero) { 
            $query = $query->excludingzero();
        }

        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目
                // // 子テーブルとなるagency_withdrawal_item_historiesレコードのカスタム項目が対象
                // $query = $query->whereHas('agency_withdrawal_item_histories.v_agency_withdrawal_item_history_custom_values', function ($q) use ($key, $val) {
                //     $q->where('key', $key)->where('val', 'like', "%$val%");
                // });

                $query = $query->where(function ($q1) use ($key, $val) {
                    // 子テーブルとなるagency_withdrawal_item_historiesレコードのカスタム項目と、agency_withdrawal(個別出金)に紐づくv_agency_withdrawal_custom_valuesのカスタム項目が対象
                    $q1->whereHas('agency_withdrawal_item_histories.v_agency_withdrawal_item_history_custom_values', function ($q2) use ($key, $val) {
                        $q2->where('key', $key)->where('val', 'like', "%$val%");
                    })->orWhereHas('agency_withdrawal_item_histories.agency_withdrawal.v_agency_withdrawal_custom_values', function ($q3) use ($key, $val) {
                        $q3->where('key', $key)->where('val', 'like', "%$val%");
                    });
                });

            // 「予約ID」と「行程ID」は必須パラメータにつき曖昧検索はしない。「仕入先ID」は任意、値がある場合は曖昧検索はしない
            } elseif (in_array($key, ['reserve_id', 'reserve_itinerary_id', 'supplier_id'], true)) { // 予約ID/行程ID/仕入先ID
                $query = $query->where($key, $val);
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

        return $query->where('account_payable_items.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    /**
     * 当該支払先を除く行程行を削除
     *
     * @param int $reserveItineraryId 行程ID
     * @param array $supplierIds 仕入先ID一覧。当リストに含まれる仕入先は削除対象外
     * @param bool $isSoftDelete 論理削除の場合はTrue
     */
    public function deleteExceptSupplierIdsForReserveItineraryId(int $reserveItineraryId, array $supplierIds, bool $isSoftDelete = true) : bool
    {
        // 支払いに関係しない行程＆仕入先行を削除。supplierIdsは仕入先IDのセーフIDリスト
        $ids = $this->accountPayableItem->select(['id'])->where('reserve_itinerary_id', $reserveItineraryId)->whereNotIn('supplier_id', $supplierIds)->pluck('id');
        if ($ids->isNotEmpty()) {
            if ($isSoftDelete) {
                $this->accountPayableItem->whereIn('id', $ids->toArray())->delete();
            } else {
                $this->accountPayableItem->whereIn('id', $ids->toArray())->forceDelete();
            }
        }
        return true;
    }
}
