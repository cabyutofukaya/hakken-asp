<?php
namespace App\Repositories\VReserveInvoice;

use App\Models\VReserveInvoice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class VReserveInvoiceRepository implements VReserveInvoiceRepositoryInterface
{
    /**
    * @param object $vReserveInvoice
    */
    public function __construct(VReserveInvoice $vReserveInvoice)
    {
        $this->vReserveInvoice = $vReserveInvoice;
    }

    public function findByReserveBundleInvoiceId(int $reserveBundleInvoiceId, array $with = [], array $select=[]) : ?VReserveInvoice
    {
        $query = $this->vReserveInvoice;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->where('reserve_bundle_invoice_id', $reserveBundleInvoiceId)->first();
    }

    public function findByReserveInvoiceId(int $reserveInvoiceId, array $with = [], array $select=[]) : ?VReserveInvoice
    {
        $query = $this->vReserveInvoice;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->where('reserve_invoice_id', $reserveInvoiceId)->first();
    }

    /**
     * ページネーション で取得
     *
     * @var $limit
     * @param bool $getDeletedReserve 削除済み予約を取得対象にする場合はtrue
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select, bool $getDeletedReserve = false) : LengthAwarePaginator
    {
        $query = $this->vReserveInvoice;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目
                $query = $query->where(function ($q) use ($key, $val) {
                    // 子テーブルとなるagency_depositsレコードのカスタム項目が対象
                    $q->whereHas('agency_deposits.v_agency_deposit_custom_values', function ($q) use ($key, $val) {
                        $q->where('key', $key)->where('val', 'like', "%$val%");
                    })
                    // 子テーブルとなるagency_bundle_depositsレコードのカスタム項目が対象
                    ->orWhereHas('agency_bundle_deposits.v_agency_bundle_deposit_custom_values', function ($q) use ($key, $val) {
                        $q->where('key', $key)->where('val', 'like', "%$val%");
                    });
                });
            } elseif ($key === 'reserve_number') { // 予約番号
                $query = $query->whereHas('reserve', function ($q) use ($key, $val) {
                    $q->where('control_number', 'like', "%$val%");
                });
            } elseif ($key=== 'issue_date_from') { // 発行日(From)
                $query = $query->where('issue_date', '>=', $val);
            } elseif ($key=== 'issue_date_to') { // 発行日(To)
                $query = $query->where('issue_date', '<=', $val);
            } elseif ($key=== 'payment_deadline_from') { // 支払期限(From)
                $query = $query->where('payment_deadline', '>=', $val);
            } elseif ($key=== 'payment_deadline_to') { // 支払期限(To)
                $query = $query->where('payment_deadline', '<=', $val);
            } elseif ($key==='status') {
                if ($val == config("consts.management_invoices.STATUS_NOT_DEPOSITED")) {
                    //
                }
            } else { // それ以外。applicant_nameなど
                $query = $query->where($key, 'like', "%$val%");
            }
        }

        $query = $getDeletedReserve ? $query : $query->whereNull('reserve_deleted_at');

        return $query->where('v_reserve_invoices.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }
}
