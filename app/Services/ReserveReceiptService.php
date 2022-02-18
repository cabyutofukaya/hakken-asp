<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\ApplicantInterface;
use App\Models\Reserve;
use App\Models\ReserveConfirm;
use App\Models\ReserveInvoice;
use App\Models\ReserveReceipt;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\ReserveReceipt\ReserveReceiptRepository;
use App\Services\DocumentRequestService;
use App\Services\ReserveBundleInvoiceService;
use App\Services\ReserveService;
use App\Services\DocumentReceiptService;
use App\Services\ReserveReceiptSequenceService;
use App\Traits\BusinessFormTrait;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ReserveReceiptService extends ReserveReceiptBaseService implements DocumentAddressInterface
{
    use BusinessFormTrait;

    public function __construct(ReserveReceiptRepository $reserveReceiptRepository, AgencyRepository $agencyRepository, ReserveService $reserveService, DocumentRequestService $documentRequestService, ReserveBundleInvoiceService $reserveBundleInvoiceService, DocumentReceiptService $documentReceiptService, ReserveReceiptSequenceService $reserveReceiptSequenceService)
    {
        $this->reserveReceiptRepository = $reserveReceiptRepository;
        $this->agencyRepository = $agencyRepository;
        $this->reserveService = $reserveService;
        $this->documentRequestService = $documentRequestService;
        $this->reserveBundleInvoiceService = $reserveBundleInvoiceService;
        $this->documentReceiptService = $documentReceiptService;
        $this->reserveReceiptSequenceService = $reserveReceiptSequenceService;
    }

    /**
     * idから一件取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReserveReceipt
    {
        return $this->reserveReceiptRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * 請求IDから一件取得
     *
     * @param int $reserveInvoiceId 請求ID
     */
    public function findByReserveInvoiceId(int $reserveInvoiceId, array $with = [], array $select=[], bool $getDeleted = false) : ?ReserveReceipt
    {
        return $this->reserveReceiptRepository->findWhere([
            'reserve_invoice_id' => $reserveInvoiceId,
        ], [], [], false);
    }

    public function create(array $data)
    {
        if(Arr::get($data, 'receipt_number')){ // 領収番号がない場合はセット
            $data['receipt_number'] = $this->createReceiptNumber(Arr::get($data, 'agency_id'));
            $data['user_receipt_number'] = $data['receipt_number'];
        }

        return $this->reserveReceiptRepository->create($data);
    }

    /**
     * 新規登録or更新
     * 
     * @param bool $checkUpdatedAt 更新日をチェックする場合はtrue
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function upsert(?ReserveReceipt $oldReserveReceipt, array $input, bool $checkUpdatedAt = true) : ReserveReceipt
    {
        if ($checkUpdatedAt && $oldReserveReceipt && $oldReserveReceipt->updated_at != Arr::get($input, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        // 宛先区分が法人でない場合はbusiness_user_idを確実にクリアしておく
        if (Arr::get($input, 'document_address.type') !== config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {
            $input['business_user_id'] = null;
        }

        return $this->reserveReceiptRepository->updateOrCreate(['agency_id' => Arr::get($input, 'agency_id'), 'reserve_id' => Arr::get($input, 'reserve_id')], $input);
    }

    ////////// interface

    /**
     * 宛名情報をクリア（宛名情報＆法人顧客ID）
     */
    public function clearDocumentAddress(int $reserveId) : bool
    {
        return $this->reserveReceiptRepository->clearDocumentAddress($reserveId);
    }
}
