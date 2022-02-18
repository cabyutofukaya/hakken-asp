<?php

namespace App\Services;

use App\Models\VReserveInvoice;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\VReserveInvoice\VReserveInvoiceRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class VReserveInvoiceService
{
    public function __construct(AgencyRepository $agencyRepository, VReserveInvoiceRepository $vReserveInvoiceRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->vReserveInvoiceRepository = $vReserveInvoiceRepository;
    }

    /**
     * reserve_bundle_invoice_idから一件取得
     */
    public function findByReserveBundleInvoiceId(int $reserveBundleInvoiceId, array $with = [], array $select=[]) : ?VReserveInvoice
    {
        return $this->vReserveInvoiceRepository->findByReserveBundleInvoiceId($reserveBundleInvoiceId, $with, $select);
    }

    /**
     * reserve_invoice_idから一件取得
     */
    public function findByReserveInvoiceId(int $reserveInvoiceId, array $with = [], array $select=[]) : ?VReserveInvoice
    {
        return $this->vReserveInvoiceRepository->findByReserveInvoiceId($reserveInvoiceId, $with, $select);
    }

    /**
     * 一覧を取得
     *
     * @param string $agencyAccount
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $agencyAccount, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->vReserveInvoiceRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

}
