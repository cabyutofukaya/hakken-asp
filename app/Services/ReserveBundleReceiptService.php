<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\ApplicantInterface;
use App\Models\Reserve;
use App\Models\ReserveConfirm;
use App\Models\ReserveInvoice;
use App\Models\ReserveBundleReceipt;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\ReserveBundleReceipt\ReserveBundleReceiptRepository;
use App\Services\DocumentRequestService;
use App\Services\ReserveBundleInvoiceService;
use App\Services\ReserveService;
use App\Services\ReserveReceiptSequenceService;
use App\Traits\BusinessFormTrait;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ReserveBundleReceiptService extends ReserveReceiptBaseService
{
    use BusinessFormTrait;

    public function __construct(ReserveBundleReceiptRepository $reserveBundleReceiptRepository, AgencyRepository $agencyRepository, ReserveService $reserveService, DocumentRequestService $documentRequestService, ReserveBundleInvoiceService $reserveBundleInvoiceService, ReserveReceiptSequenceService $reserveReceiptSequenceService)
    {
        $this->reserveBundleReceiptRepository = $reserveBundleReceiptRepository;
        $this->agencyRepository = $agencyRepository;
        $this->reserveService = $reserveService;
        $this->documentRequestService = $documentRequestService;
        $this->reserveBundleInvoiceService = $reserveBundleInvoiceService;
        $this->reserveReceiptSequenceService = $reserveReceiptSequenceService;
    }

    /**
     * idから一件取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReserveBundleReceipt
    {
        return $this->reserveBundleReceiptRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * 新規登録or更新
     * 
     * @param bool $checkUpdatedAt 更新日をチェックする場合はtrue
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function upsert(?ReserveBundleReceipt $oldReserveBundleReceipt, array $input, bool $checkUpdatedAt = true) : ReserveBundleReceipt
    {
        if ($checkUpdatedAt && $oldReserveBundleReceipt && $oldReserveBundleReceipt->updated_at != Arr::get($input, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        return $this->reserveBundleReceiptRepository->updateOrCreate(['agency_id' => Arr::get($input, 'agency_id'), 'reserve_bundle_invoice_id' => Arr::get($input, 'reserve_bundle_invoice_id')], $input);
    }

    /**
     * 一括請求IDから当該レコードを一件取得
     */
    public function findByReserveBundleInvoiceId(int $reserveBundleInvoiceId) : ?ReserveBundleReceipt
    {
        return $this->reserveBundleReceiptRepository->findWhere(['reserve_bundle_invoice_id' => $reserveBundleInvoiceId]);
    }

    /**
     * ステータス更新
     */
    public function updateStatus(int $id, int $status) : bool
    {
        return $this->reserveBundleReceiptRepository->updateStatus($id, $status);
    }
}
