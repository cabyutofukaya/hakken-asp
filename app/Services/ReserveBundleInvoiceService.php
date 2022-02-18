<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\ReserveBundleInvoice;
use App\Models\ReserveInvoice;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\ReserveBundleInvoice\ReserveBundleInvoiceRepository;
use App\Services\DocumentRequestAllService;
use App\Services\BusinessUserService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use App\Traits\BusinessFormTrait;
use Illuminate\Support\Collection;

class ReserveBundleInvoiceService extends ReserveDocumentService
{
    use BusinessFormTrait;

    public function __construct(ReserveBundleInvoiceRepository $reserveBundleInvoiceRepository, AgencyRepository $agencyRepository, DocumentRequestAllService $documentRequestAllService, BusinessUserService $businessUserService)
    {
        $this->reserveBundleInvoiceRepository = $reserveBundleInvoiceRepository;
        $this->agencyRepository = $agencyRepository;
        $this->documentRequestAllService = $documentRequestAllService;
        $this->businessUserService = $businessUserService;
    }

    /**
     * 当該IDを取得
     */
    public function find(int $id, array $with = [], array $select = [], bool $getDeleted = false) : ReserveBundleInvoice
    {
        return $this->reserveBundleInvoiceRepository->find($id, $with, $select, $getDeleted);
    }
    
    /**
     * 一括請求情報が存在するか
     *
     * @param int $businessUserId 法人顧客ID
     * @param string $cutoffDate 請求締日（YYYY-MM-DD）
     * @return bool
     */
    public function isExistInvoice(int $businessUserId, string $cutoffDate) : bool
    {
        return $this->reserveBundleInvoiceRepository->isExistInvoice($businessUserId, $cutoffDate);
    }

    /**
     * 法人顧客IDと請求対象月からレコードを一件取得
     *
     * @param int $businessUserId 法人顧客ID
     * @param string $cutoffDate 請求締日
     * @return ReserveBundleInvoice
     */
    public function findByCutoffDateInfo(int $businessUserId, string $cutoffDate) : ?ReserveBundleInvoice
    {
        return $this->reserveBundleInvoiceRepository->findWhere([
            'business_user_id' => $businessUserId,
            'cutoff_date' => $cutoffDate
        ]);
    }

    /**
     * 作成
     */
    public function create(array $data) : ReserveBundleInvoice
    {
        return $this->reserveBundleInvoiceRepository->create($data);
    }

    /**
     * 更新
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $reserveBundleInvoiceId, array $input) : ReserveBundleInvoice
    {
        $oldReserveBundleInvoice = $this->find($reserveBundleInvoiceId);
        if ($oldReserveBundleInvoice && $oldReserveBundleInvoice->updated_at != Arr::get($input, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        $input['billing_address_name'] = Arr::get($input, 'document_address.company_name'); // 請求先名。検索用に保存

        $newReserveBundleInvoice = $this->reserveBundleInvoiceRepository->update($reserveBundleInvoiceId, $input);

        // 入金額の再計算
        $this->updateFields(
            $newReserveBundleInvoice->id,
            [
                'deposit_amount' => $newReserveBundleInvoice->sum_deposit,
                'not_deposit_amount' => $newReserveBundleInvoice->sum_not_deposit,
            ]
        );

        return $newReserveBundleInvoice;
    }

    /**
     * 一括請求レコードを作成
     *
     */
    public function createDefaultData(ReserveInvoice $reserveInvoice) : ReserveBundleInvoice
    {
        // 有効担当者IDを取得（削除済も取得）
        $partnerManagerIds = $this->getDefaultPartnerManagerCheckIds($this->businessUserService->getManagers($reserveInvoice->business_user_id, true));

        // 一括請求のデフォルト帳票設定
        $documentRequestAll = $this->documentRequestAllService->getDefault($reserveInvoice->agency_id);

        // 書類設定。$documentRequestAllが未設定なればsetting配列、sealプロパティ初期化
        $documentSetting = $this->getDocumentSettingSealOrInitSetting($documentRequestAll->toArray());

        // 「検印欄」の表示・非表示は設定がイレギュラーにつき、他の設定項目と形式を合わせる
        $this->setSealSetting($documentSetting, config('consts.document_request_alls.DISPLAY_BLOCK'));

        $documentAddress = $this->getDocumentAddressByBusinessUser($reserveInvoice->business_user);

        // 一括請求書番号。システムで特に管理する値でもないのでとりあえず入れておく程度もの
        $bundle_invoice_number = $this->createBundleInvoiceNumber($reserveInvoice->agency_id, $reserveInvoice->return_date);

        return $this->create([
            'agency_id' => $reserveInvoice->agency_id,
            'business_user_id' => $reserveInvoice->business_user_id,
            'bundle_invoice_number' => $bundle_invoice_number,
            'user_bundle_invoice_number' => $bundle_invoice_number,
            'cutoff_date' => $reserveInvoice->bundle_invoice_cutoff_date,
            'document_request_all_id' => $documentRequestAll->id,
            'document_common_id' => $documentRequestAll->document_common_id,
            'billing_address_name' => Arr::get($documentAddress, 'company_name'), // 検索用に請求書宛名保存,
            'document_address' => $documentAddress,
            'name' => config('consts.reserve_bundle_invoices.DEFAULT_NAME'),
            'last_manager_id' => $reserveInvoice->last_manager_id,
            'last_note' => $reserveInvoice->last_note,
            'partner_manager_ids' => $partnerManagerIds,
            'document_setting' => $documentSetting,
            'document_common_setting' => $documentRequestAll ? $documentRequestAll->document_common->toArray() : [], // 共通設定
            'reserve_prices' => [], // ひとまず初期化
            'amount_total' => 0, // ひとまず初期化
            'deposit_amount' => 0, // 入金済額
            'not_deposit_amount' => 0, // 未入金額
            'status' => config('consts.reserve_bundle_invoices.STATUS_DEFAULT'),
        ]);
    }

    /**
     * 料金に関連する項目を更新
     *
     * @param ReserveBundleInvoice $reserveBundleInvoice
     * @param Collection $reserveInvoices 請求データ一覧
     * @param bool
     */
    public function refreshPriceData(ReserveBundleInvoice $reserveBundleInvoice, Collection $reserveInvoices) : bool
    {
        // 請求データ一覧から担当者IDごとにまとめた配列を作成
        $reservePrices = $this->getReservePriceInfo($reserveInvoices);

        // $reservePricesと担当者ID一覧をもとに合計金額を算定
        $amountTotal = get_reserve_price_total($reserveBundleInvoice->partner_manager_ids, $reservePrices);

        // 合計金額を更新
        $this->updateFields($reserveBundleInvoice->id, [
            'reserve_prices' => $reservePrices, // 一応、計算に使ったデータを記録しておく
            'amount_total' => $amountTotal, //合計額
        ]);

        // 更新したreserveBundleInvoiceオブジェクトのリレーションを使って入金額、未入金額を更新
        $newReserveBundleInvoice = $this->find($reserveBundleInvoice->id);
        $this->updateFields($reserveBundleInvoice->id, [
            'deposit_amount' => $newReserveBundleInvoice->sum_deposit, // 入金額
            'not_deposit_amount' => $newReserveBundleInvoice->sum_not_deposit // 未入金額
        ]);

        return true;
    }

    /**
     * フィールド更新
     */
    public function updateFields(int $id, array $params) : int
    {
        return $this->reserveBundleInvoiceRepository->updateFields($id, $params);
    }

    /**
     * 子レコードがなければ削除
     */
    public function deleteIfNoChild(int $id, bool $isSoftDelete = true)
    {
        return $this->reserveBundleInvoiceRepository->deleteIfNoChild($id, $isSoftDelete);
    }

    /**
     * 請求番号を生成
     *
     * TODO ↓以下の形式は正式に決まっていないので、これで良いか確認
     * 会社識別子 + 請求月(YYYYMM)
     *
     * @param int $agencyId 会社ID
     * @param string $cutoffDate 請求締日(YYYY-MM-DD)
     * @return string
     */
    public function createBundleInvoiceNumber(int $agencyId, string $cutoffDate) : string
    {
        $agency = $this->agencyRepository->find($agencyId);

        return sprintf("%s-%s", $agency->identifier, date('Ym', strtotime($cutoffDate)));
    }
}
