<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\AgencyDeposit;
use App\Repositories\ReserveInvoice\ReserveInvoiceRepository;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\AgencyDeposit\AgencyDepositRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\AgencyDepositCustomValueService;
use App\Services\ReserveInvoiceService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Traits\UserCustomItemTrait;

class AgencyDepositService extends DepositBaseService
{
    use UserCustomItemTrait;

    public function __construct(AgencyRepository $agencyRepository, AgencyDepositRepository $agencyDepositRepository, ReserveInvoiceRepository $reserveInvoiceRepository, AgencyDepositCustomValueService $agencyDepositCustomValueService, ReserveInvoiceService $reserveInvoiceService)
    {
        $this->agencyRepository = $agencyRepository;
        $this->agencyDepositRepository = $agencyDepositRepository;
        $this->reserveInvoiceRepository = $reserveInvoiceRepository;
        $this->agencyDepositCustomValueService = $agencyDepositCustomValueService;
        $this->reserveInvoiceService = $reserveInvoiceService;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $with = [], array $select = [], bool $getDeleted = false) : AgencyDeposit
    {
        return $this->agencyDepositRepository->find($id, $with, $select, $getDeleted);
    }

    // /**
    //  * 当該支払い明細の出金額合計を取得
    //  * 行ロックで取得
    //  *
    //  * @param int $reserveInvoiceId 支払い明細ID
    //  * @return int
    //  */
    // public function getSumAmountByReserveInvoiceId(int $reserveInvoiceId, bool $isLock=false) : int
    // {
    //     return $this->agencyDepositRepository->getSumAmountByReserveInvoiceId($reserveInvoiceId, $isLock);
    // }

    /**
     * 入金登録
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     * @param bool $generateIdentifierId 入金IDを生成する場合はtrue
     * @param bool $checkReserveInvoiceUpdatedAt 請求書データが更新されているかチェックする場合はtrue
     */
    public function create(array $data, bool $generateIdentifierId = true, bool $checkReserveInvoiceUpdatedAt = true): AgencyDeposit
    {
        if ($checkReserveInvoiceUpdatedAt) { // 予約レコードの申込者が変更されているケースなどもここでエラーになる
            $reserveInvoice = $this->reserveInvoiceRepository->find((int)$data['reserve_invoice_id']);
            if ($reserveInvoice->updated_at != Arr::get($data, 'reserve_invoice.updated_at')) {
                throw new ExclusiveLockException("他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
            }
        }

        if ($generateIdentifierId) {
            // agency_bundle_depositと共有する入金IDを生成
            $data['identifier_id'] = $this->generateIdentifierId();
        }
        $agencyDeposit = $this->agencyDepositRepository->create($data);

        //////// カスタムフィールド
        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->agencyDepositCustomValueService->upsertCustomFileds($customFields, $agencyDeposit->id); // カスタムフィールド保存
        }

        //////// 最終担当、最終備考を更新
        $this->reserveInvoiceService->updateFields($agencyDeposit->reserve_invoice_id, [
            'last_manager_id' => $agencyDeposit->manager_id,
            'last_note' => $agencyDeposit->note
        ]); // reserve_invoicesテーブルの最終更新担当者と最終更新備考を更新


        return $agencyDeposit;
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->agencyDepositRepository->delete($id, $isSoftDelete);
    }

    /**
     * 識別IDを指定して削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function deleteByIdentifierId(string $identifierId, bool $isSoftDelete = true): bool
    {
        return $this->agencyDepositRepository->deleteByIdentifierId($identifierId, $isSoftDelete);
    }
}
