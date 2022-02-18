<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\AgencyBundleDeposit;
use App\Repositories\ReserveBundleInvoice\ReserveBundleInvoiceRepository;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\AgencyBundleDeposit\AgencyBundleDepositRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\AgencyBundleDepositCustomValueService;
use App\Services\ReserveBundleInvoiceService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Traits\UserCustomItemTrait;

class AgencyBundleDepositService extends DepositBaseService
{
    use UserCustomItemTrait;

    public function __construct(AgencyRepository $agencyRepository, AgencyBundleDepositRepository $agencyBundleDepositRepository, ReserveBundleInvoiceRepository $reserveBundleInvoiceRepository, AgencyBundleDepositCustomValueService $agencyBundleDepositCustomValueService, ReserveBundleInvoiceService $reserveBundleInvoiceService)
    {
        $this->agencyRepository = $agencyRepository;
        $this->agencyBundleDepositRepository = $agencyBundleDepositRepository;
        $this->reserveBundleInvoiceRepository = $reserveBundleInvoiceRepository;
        $this->agencyBundleDepositCustomValueService = $agencyBundleDepositCustomValueService;
        $this->reserveBundleInvoiceService = $reserveBundleInvoiceService;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ?AgencyBundleDeposit
    {
        return $this->agencyBundleDepositRepository->find($id, $with, $select, $getDeleted);
    }

    // /**
    //  * 当該支払い明細の出金額合計を取得
    //  * 行ロックで取得
    //  *
    //  * @param int $reserveBundleInvoiceId 支払い明細ID
    //  * @return int
    //  */
    // public function getSumAmountByReserveBundleInvoiceId(int $reserveBundleInvoiceId, bool $isLock=false) : int
    // {
    //     return $this->agencyBundleDepositRepository->getSumAmountByReserveBundleInvoiceId($reserveBundleInvoiceId, $isLock);
    // }

    /**
     * 入金登録
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     * @param bool $generateIdentifierId 入金IDを生成する場合はtrue
     * @param bool $checkReserveBundleInvoiceUpdatedAt 請求書データが更新されているかチェックする場合はTrue
     */
    public function create(array $data, bool $generateIdentifierId = true, bool $checkReserveBundleInvoiceUpdatedAt = true) : AgencyBundleDeposit
    {
        if ($checkReserveBundleInvoiceUpdatedAt) {
            $reserveBundleInvoice = $this->reserveBundleInvoiceRepository->find((int)$data['reserve_bundle_invoice_id']);
            if ($reserveBundleInvoice->updated_at != Arr::get($data, 'reserve_bundle_invoice.updated_at')) {
                throw new ExclusiveLockException("他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
            }
        }

        if ($generateIdentifierId) {
            // agency_depositと共有する入金IDを生成
            $data['identifier_id'] = $this->generateIdentifierId();
        }
        $agencyBundleDeposit = $this->agencyBundleDepositRepository->create($data);

        //////// カスタムフィールド
        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->agencyBundleDepositCustomValueService->upsertCustomFileds($customFields, $agencyBundleDeposit->id); // カスタムフィールド保存
        }

        //////// 最終担当、最終備考を更新
        $this->reserveBundleInvoiceService->updateFields($agencyBundleDeposit->reserve_bundle_invoice_id, [
            'last_manager_id' => $agencyBundleDeposit->manager_id,
            'last_note' => $agencyBundleDeposit->note
        ]); // reserve_bundle_invoicesテーブルの最終更新担当者と最終更新備考を更新

        return $agencyBundleDeposit;
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete = true): bool
    {
        return $this->agencyBundleDepositRepository->delete($id, $isSoftDelete);
    }

    /**
     * 識別IDを指定して削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function deleteByIdentifierId(string $identifierId, bool $isSoftDelete = true): bool
    {
        return $this->agencyBundleDepositRepository->deleteByIdentifierId($identifierId, $isSoftDelete);
    }
}
