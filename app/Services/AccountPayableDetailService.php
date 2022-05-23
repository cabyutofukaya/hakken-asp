<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\AccountPayableDetail;
use App\Models\Supplier;
use App\Repositories\AccountPayableDetail\AccountPayableDetailRepository;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\AgencyWithdrawal\AgencyWithdrawalRepository;
use App\Traits\PaymentTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AccountPayableDetailService implements AccountPayableInterface
{
    use PaymentTrait;
    
    public function __construct(AgencyRepository $agencyRepository, AccountPayableDetailRepository $accountPayableDetailRepository, AgencyWithdrawalRepository $agencyWithdrawalRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->accountPayableDetailRepository = $accountPayableDetailRepository;
        $this->agencyWithdrawalRepository = $agencyWithdrawalRepository;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     * @param bool $isLock 行ロックして取得する場合はtrue
     */
    public function find(int $id, array $with = [], array $select=[], bool $isLock = false) : ?AccountPayableDetail
    {
        return $this->accountPayableDetailRepository->find($id, $with, $select, $isLock);
    }

    /**
     * 検索して一件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]): ?AccountPayableDetail
    {
        return $this->accountPayableDetailRepository->findWhere($where, $with, $select);
    }

    /**
     * 検索条件にマッチするレコードがあるか
     */
    public function whereExists(array $where) : bool
    {
        return $this->accountPayableDetailRepository->whereExists($where);
    }

    /**
     * 仕入先＆商品毎にまとめるための条件クエリを取得
     *
     * @param array $columnVals 対応カラム名と値の配列
     */
    public function getSummarizeItemQuery(array $columnVals)
    {
        $where = [];

        foreach (config("consts.account_payable_items.ITEM_PAYABLE_NUMBER_COLUMNS") as $col) {
            $where[$col] = $columnVals[$col];
        }

        return $this->accountPayableDetailRepository->getSummarizeItemQuery($where);
    }

    /**
     * 商品毎レコードの一括金額＆ステータス更新
     *
     * @param int $amount 出金額
     * @param int $withdrawalRate 出金額比率
     * @param array $columnVals
     */
    public function batchRefreshAmountForItem(array $columnVals) : bool
    {
        $updateParams = [];

        $this->getSummarizeItemQuery($columnVals)
            ->with(['v_agency_withdrawal_total:account_payable_detail_id,total_amount'])
            ->select(['id', 'amount_billed','unpaid_balance', 'status'])
            // ->lockForUpdate()
            ->chunk(300, function ($rows) use (&$updateParams) { // 念の為300件ずつ取得
            foreach ($rows as $row) {
                $tmp = [];

                $amountPayment = data_get($row, 'v_agency_withdrawal_total.total_amount', 0); // 支払額

                $tmp['id'] = $row->id;
                $tmp['amount_billed'] = $row->amount_billed;
                $tmp['amount_payment'] = $amountPayment;
                $tmp['unpaid_balance'] = $row->amount_billed - $amountPayment; // 未払額を計算
                $tmp['status'] = $this->getPaymentStatus($tmp['unpaid_balance'], $tmp['amount_billed'], 'account_payable_details'); // ステータス更新

                $updateParams[] = $tmp;
            }
        });

        foreach (array_chunk($updateParams, 1000) as $rows) { // 念の為1000件ずつ処理
            // 対象行の残高、ステータスをバルクアップデート
            $this->accountPayableDetailRepository->updateBulk($rows, 'id');
        }

        return true;
    }

    /**
     * 一覧を取得
     *
     * @param string $agencyAccount
     * @param string $subject 科目
     * @param int $applicationStep 予約段階（見積/予約）
     * @param int $limit
     * @param array $with
     * @param bool $exZero 仕入額・未払い額が0円のレコードを取得しない場合はtrue
     * @param
     */
    public function paginateByAgencyAccount(string $agencyAccount, string $subject, array $params, int $limit, ?string $applicationStep = null, array $with = [], array $select=[], bool $exZero = true) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->accountPayableDetailRepository->paginateByAgencyId($agencyId, $subject, $params, $limit, $applicationStep, $with, $select, $exZero);
    }

    /**
     * 更新
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $data): AccountPayableDetail
    {
        $accountPayableDetail = $this->accountPayableDetailRepository->find($id);
        if ($accountPayableDetail->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        return $this->accountPayableDetailRepository->update($id, $data);
    }

    /**
     * 未払い金額とステータスを更新
     *
     * @param int $id account_payable_detail_id
     * @param int $amountPayment 支払額
     * @param int $unpaidBalance 未払額
     * @param int $status 支払いステータス
     */
    public function updateStatusAndPaidBalance($id, int $amountPayment, int $unpaidBalance, $status) : Model
    {
        return $this->accountPayableDetailRepository->updateField(
            $id,
            [
                'amount_payment' => $amountPayment,
                'unpaid_balance' => $unpaidBalance,
                'status' => $status
            ]
        );
    }

    /**
     * 登録or更新
     *
     */
    public function updateOrCreate(array $where, array $params) : ?AccountPayableDetail
    {
        return $this->accountPayableDetailRepository->updateOrCreate($where, $params);
    }

    /**
     * 任意のフィールドを更新
     *
     * @param int $id account_payable_details ID
     */
    public function updateFields(int $id, $params)
    {
        return $this->accountPayableDetailRepository->updateField($id, $params);
    }

    /**
     * 支払日を更新
     */
    public function updatePaymentDate(int $reserveId, int $supplierId, string $paymentDate)
    {
        $this->accountPayableDetailRepository->updateWhere(
            ['payment_date' => $paymentDate],
            ['reserve_id' => $reserveId, 'supplier_id' => $supplierId]
        );

        return true;
    }

    /**
     * 仕入先へのキャンセルチャージ料金を設定
     *
     * @param int $cancelChargeNet キャンセルチャージNet金額
     * @param string $saleableType 販売種別
     * @param int $saleableId 販売ID
     * @param bool $getUpdatedId 更新対象のレコードIDがを取得する場合はtrue
     */
    public function setCancelChargeBySaleableId(int $cancelChargeNet, string $saleableType, int $saleableId, bool $getUpdatedId = false) : ?int
    {
        $updatedId = null;
        if ($getUpdatedId) {
            $res = $this->accountPayableDetailRepository->findWhere(['saleable_type' => $saleableType, 'saleable_id' => $saleableId], [], ['id']);
            $updatedId = $res->id;
        }
        $this->accountPayableDetailRepository->updateWhere(
            ['amount_billed' => $cancelChargeNet],
            ['saleable_type' => $saleableType, 'saleable_id' => $saleableId]
        );

        return $updatedId;
    }

    /**
     * 仕入先のキャンセルチャージ仕入料金を更新(バルクアップデート)
     *
     * @param string $saleableType 科目タイプ
     * @param array $params 更新パラメータ
     * @param string $id 更新ID
     */
    public function setAmountBilledBulk(string $saleableType, array $params, string $id) : bool
    {
        return $this->accountPayableDetailRepository->updateWhereBulk(['saleable_type' => $saleableType], $params, $id);
    }

    /**
     * 対象予約IDの仕入情報を取得
     */
    public function getByReserveId(int $reserveId, array $with = [], array $select = []) : Collection
    {
        return $this->accountPayableDetailRepository->getWhere(['reserve_id' => $reserveId], $with, $select);
    }

    /**
     * 検索して全件取得
     */
    public function getWhere(array $where, array $with=[], array $select=[]) : Collection
    {
        return $this->accountPayableDetailRepository->getWhere($where, $with, $select);
    }

    /**
     * 当該仕入IDリストに紐づくid一覧を取得
     *
     * @param string $saleableType 仕入科目
     * @param array $saleableIds 仕入科目ID一覧
     */
    public function getBySaleableIds(string $saleableType, array $saleableIds, array $with=[], array $select=['id']) : Collection
    {
        return $this->accountPayableDetailRepository->getBySaleableIds($saleableType, $saleableIds, $with, $select);
    }
    
    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->accountPayableDetailRepository->delete($id, $isSoftDelete);
    }

    /**
     * バルクインサート
     */
    public function insert(array $params) : bool
    {
        return $this->accountPayableDetailRepository->insert($params);
    }

    /**
     * バルクアップデート
     *
     * @param array $params
     */
    public function updateBulk(array $params, string $id = "id") : bool
    {
        return $this->accountPayableDetailRepository->updateBulk($params, $id);
    }
}
