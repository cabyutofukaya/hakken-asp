<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\ApplicantInterface;
use App\Models\Reserve;
use App\Models\ReserveConfirm;
use App\Models\ReserveInvoice;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\ReserveInvoice\ReserveInvoiceRepository;
use App\Services\DocumentRequestService;
use App\Services\ReserveBundleInvoiceService;
use App\Services\ReserveInvoiceSequenceService;
use App\Services\ReserveService;
use App\Traits\BusinessFormTrait;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ReserveInvoiceService extends ReserveDocumentService implements DocumentAddressInterface
{
    use BusinessFormTrait;

    public function __construct(ReserveInvoiceRepository $reserveInvoiceRepository, ReserveInvoiceSequenceService $reserveInvoiceSequenceService, AgencyRepository $agencyRepository, ReserveService $reserveService, DocumentRequestService $documentRequestService, ReserveBundleInvoiceService $reserveBundleInvoiceService)
    {
        $this->reserveInvoiceRepository = $reserveInvoiceRepository;
        $this->reserveInvoiceSequenceService = $reserveInvoiceSequenceService;
        $this->agencyRepository = $agencyRepository;
        $this->reserveService = $reserveService;
        $this->documentRequestService = $documentRequestService;
        $this->reserveBundleInvoiceService = $reserveBundleInvoiceService;
    }

    // 請求データ
    private function createData(
        ?int $businessUserId,
        ?string $invoiceNumber,
        ?string $userInvoiceNumber,
        ?string $issueDate,
        ?string $paymentDeadline,
        ?int $documentRequestId,
        ?int $documentCommonId,
        ?array $documentAddress,
        ?string $billingAddressName,
        ?string $applicantName,
        ?string $name,
        ?string $departureDate,
        ?string $returnDate,
        int $managerId,
        ?array $representative,
        ?array $participantIds,
        ?array $documentSetting, // 書類設定
        ?array $documentCommonSetting, // 共通設定
        ?array $optionPrices,
        ?array $airticketPrices,
        ?array $hotelPrices,
        ?array $hotelInfo,
        ?array $hotelContacts,
        $status
    ) : array {
        // 合計金額
        $amountTotal = get_price_total($participantIds, $optionPrices, $airticketPrices, $hotelPrices);

        return [
            'business_user_id' => $businessUserId,
            'invoice_number' => $invoiceNumber,
            'user_invoice_number' => $userInvoiceNumber,
            'issue_date' => $issueDate,
            'payment_deadline' => $paymentDeadline,
            'document_request_id' => $documentRequestId,
            'document_common_id' => $documentCommonId,
            'document_address' => $documentAddress,
            'billing_address_name' => $billingAddressName,
            'applicant_name' => $applicantName,
            'name' => $name,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'last_manager_id' => $managerId,
            'representative' => $representative,
            'participant_ids' => $participantIds,
            'document_setting' => $documentSetting,
            'document_common_setting' => $documentCommonSetting,
            'option_prices' => $optionPrices,
            'airticket_prices' => $airticketPrices,
            'hotel_prices' => $hotelPrices,
            'hotel_info' => $hotelInfo,
            'hotel_contacts' => $hotelContacts,
            'amount_total' => $amountTotal,
            'deposit_amount' => 0,
            'not_deposit_amount' => $amountTotal, // 未入金額も合計金額で初期化
            'status' => $status,
        ];
    }

    /**
     * "予約確認書"をもとに請求レコードを作成
     *
     * @param int $managerId 作業中のスタッフID
     */
    public function createFromReserveConfirm(ReserveConfirm $reserveConfirm, Reserve $reserve, int $reserveItineraryId, int $managerId)
    {
        // 参加者IDを取得
        $participantIds = $reserveConfirm->participant_ids;

        // 請求書書類設定
        $documentRequest = $this->documentRequestService->getDefault($reserve->agency_id);
        
        // 書類設定。$documentRequestが未設定なればsetting配列、sealプロパティ初期化
        $documentSetting = $this->getDocumentSettingSealOrInitSetting($documentRequest->toArray());

        // 「検印欄」の表示・非表示は設定がイレギュラーにつき、他の設定項目と形式を合わせる
        $this->setSealSetting($documentSetting, config('consts.document_requests.DISPLAY_BLOCK'));

        /////////// 入力初期値をセット ///////////

        $invoiceNumber = $this->createInvoiceNumber($reserve->agency_id);

        // 宛名設定
        $documentAddress = $reserveConfirm->document_address;

        $businessUserId = null; // 法人顧客ID
        $billingAddressName = null; // 請求先名(検索用)
        
        if ($reserve->applicantable && $reserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {
            $businessUserId = $reserve->applicantable->business_user_id;
            $billingAddressName = Arr::get($documentAddress, 'company_name');
        } elseif ($reserve->applicantable && $reserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_PERSON')) {
            $billingAddressName = Arr::get($documentAddress, 'name');
        }

        $reserveInvoice = $this->reserveInvoiceRepository->updateOrCreate(
            [
                'agency_id' => $reserveConfirm->agency_id,
                'reserve_id' => $reserveConfirm->reserve_id,
                'reserve_itinerary_id' => $reserveItineraryId
            ],
            $this->createData(
                $businessUserId,
                $invoiceNumber,
                $invoiceNumber, // 初期値は$invoiceNumberと同じに
                null,
                null,
                $documentRequest->id ?? null,
                $documentRequest->document_common_id ?? null,
                $documentAddress,
                $billingAddressName,
                Arr::get($documentAddress, 'name'),
                $reserveConfirm->name,
                $reserveConfirm->departure_date,
                $reserveConfirm->return_date,
                $managerId,
                $reserveConfirm->representative,
                $reserveConfirm->participant_ids,
                $documentSetting, // 書類設定
                $documentRequest ? $documentRequest->document_common->toArray() : [],
                $reserveConfirm->option_prices,
                $reserveConfirm->airticket_prices,
                $reserveConfirm->hotel_prices,
                $reserveConfirm->hotel_info,
                $reserveConfirm->hotel_contacts,
                config('consts.reserve_invoices.STATUS_DEFAULT')
            )
        );

        // 一括請求関連処理（作成、リレーション設定等）
        $this->reserveBundleInvoiceRefresh(null, $reserveInvoice);

        return $this->find($reserveInvoice->id);
    }

    /**
     * "予約情報"をもとに請求レコードを作成
     *
     * 予約確認書が作成される前に請求書が作成されることはないので、基本的にはこのメソッドが使用されることはないハズ。createFromReserveConfirmで処理される想定
     *
     * @param int $managerId 作業中のスタッフID
     */
    public function createFromReserve(Reserve $reserve, int $reserveItineraryId, int $managerId) : ReserveInvoice
    {
        // 有効参加者IDを取得
        $participantIds = $this->getDefaultParticipantCheckIds($this->reserveService->getParticipants($reserve->id, true));

        // オプション価格情報、航空券価格情報、ホテル価格情報、宿泊施設情報、宿泊施設連絡先を取得
        list($optionPrices, $airticketPrices, $hotelPrices, $hotelInfo, $hotelContacts) = $this->getPriceAndHotelInfo($reserve->enabled_reserve_itinerary->id ? $reserve->enabled_reserve_itinerary : null, false);

        // 請求書書類設定
        $documentRequest = $this->documentRequestService->getDefault($reserve->agency_id);
        
        // 書類設定。$documentRequestが未設定なればsetting配列、sealプロパティ初期化
        $documentSetting = $this->getDocumentSettingSealOrInitSetting($documentRequest->toArray());

        // 「検印欄」の表示・非表示は設定がイレギュラーにつき、他の設定項目と形式を合わせる
        $this->setSealSetting($documentSetting, config('consts.document_requests.DISPLAY_BLOCK'));

        /////////// 入力初期値をセット ///////////

        $invoiceNumber = $this->createInvoiceNumber($reserve->agency_id);

        // 宛名設定
        $documentAddress = $this->getDocumentAddress($reserve->applicantable);

        $businessUserId = null; // 法人顧客ID
        $billingAddressName = null; // 請求先名(検索用)
        
        if ($reserve->applicantable && $reserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {
            $businessUserId = $reserve->applicantable->business_user_id;
            $billingAddressName = Arr::get($documentAddress, 'company_name');
        } elseif ($reserve->applicantable && $reserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_PERSON')) {
            $billingAddressName = Arr::get($documentAddress, 'name');
        }

        $reserveInvoice = $this->reserveInvoiceRepository->updateOrCreate(
            [
                'agency_id' => $reserve->agency_id,
                'reserve_id' => $reserve->id,
                'reserve_itinerary_id' => $reserveItineraryId
            ],
            $this->createData(
                $businessUserId,
                $invoiceNumber,
                $invoiceNumber, // 初期値は$invoiceNumberと同じに
                null,
                null,
                $documentRequest->id ?? null,
                $documentRequest->document_common_id ?? null,
                $documentAddress,
                $billingAddressName,
                Arr::get($documentAddress, 'name'),
                $reserve->name,
                $reserve->departure_date,
                $reserve->return_date,
                $managerId,
                $this->getRepresentativeInfo($reserve),
                $participantIds,
                $documentSetting, // 書類設定
                $documentRequest ? $documentRequest->document_common->toArray() : [],
                $optionPrices,
                $airticketPrices,
                $hotelPrices,
                $hotelInfo,
                $hotelContacts,
                config('consts.reserve_invoices.STATUS_DEFAULT')
            )
        );

        // 一括請求関連処理（作成、リレーション設定等）
        $this->reserveBundleInvoiceRefresh(null, $reserveInvoice);

        return $this->find($reserveInvoice->id);
    }

    /**
     * 請求書作成or更新後の一括請求レコード作成や請求書レコードとの紐付け処理等
     *
     * @param int $oldReserveBundleInvoiceId 紐付け変更前のreserve_bundle_invoice_id
     * @param ReserveInvoice $reserveInvoice 最新の請求情報
     */
    public function reserveBundleInvoiceRefresh(?int $oldReserveBundleInvoiceId, ReserveInvoice $reserveInvoice)
    {
        if ($reserveInvoice->is_corporate_customer) { //法人顧客
            if (!($reserveBundleInvoice = $this->reserveBundleInvoiceService->findByCutoffDateInfo($reserveInvoice->business_user_id, $reserveInvoice->bundle_invoice_cutoff_date))) { // 一括請求レコードがない場合は作成
                $reserveBundleInvoice = $this->reserveBundleInvoiceService->createDefaultData($reserveInvoice);
            }

            if ($reserveInvoice->reserve_bundle_invoice_id !== $reserveBundleInvoice->id) { // リレーションIDを紐付け
                $this->updateFields($reserveInvoice->id, [
                    'reserve_bundle_invoice_id' => $reserveBundleInvoice->id
                ]);
            }

            // 一括請求レコードの料金関連カラムを更新
            $this->reserveBundleInvoiceService->refreshPriceData(
                $reserveBundleInvoice,
                $this->getByReserveBundleInvoiceId(
                    $reserveBundleInvoice->agency->account,
                    $reserveBundleInvoice->id,
                    ['reserve:id,control_number,applicantable_type,applicantable_id,cancel_at','reserve.applicantable:id,name'],
                    ['reserve_id','option_prices','airticket_prices','hotel_prices','participant_ids']
                )
            );
        }
        // リレーションが無くなった一括請求レコードは削除（更新前の請求レコードの情報から調べる）
        if ($oldReserveBundleInvoiceId) {
            $this->reserveBundleInvoiceService->deleteIfNoChild($oldReserveBundleInvoiceId, true); // 論理削除
        }
    }

    /**
     * idから一件取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ?ReserveInvoice
    {
        return $this->reserveInvoiceRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * 予約IDから一件取得
     *
     * @param int $reserveId 予約ID
     */
    public function findByReserveId(int $reserveId, array $with = [], array $select=[], bool $getDeleted = false) : ?ReserveInvoice
    {
        return $this->reserveInvoiceRepository->findWhere(['reserve_id' => $reserveId], $with, $select, $getDeleted);
    }

    /**
     * 行程IDから一件取得
     *
     * @param int $reserveItineraryId 行程ID
     */
    public function findByReserveItineraryId(int $reserveItineraryId, array $with = [], array $select=[], bool $getDeleted = false) : ?ReserveInvoice
    {
        return $this->reserveInvoiceRepository->findWhere(['reserve_itinerary_id' => $reserveItineraryId], $with, $select, $getDeleted);
    }

    /**
     * 一覧を取得
     *
     * @param string $account 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByReserveBundleInvoiceId(string $agencyAccount, int $reserveBundleInvoiceId, int $limit, array $with = [], array $select = [], bool $getDeleted = false) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);

        return $this->reserveInvoiceRepository->paginateByReserveBundleInvoiceId(
            $agencyId,
            $reserveBundleInvoiceId,
            $limit,
            $with,
            $select,
            $getDeleted
        );
    }

    /**
     * 当該一括請求IDに紐づくレコードを全取得
     *
     * @param string $agencyAccount 会社アカウント
     * @param int $reserveBundleInvoiceId 一括請求ID
     */
    public function getByReserveBundleInvoiceId(string $agencyAccount, int $reserveBundleInvoiceId, array $with = [], array $select = [], bool $getDeleted = false) : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);

        return $this->reserveInvoiceRepository->getWhere(
            [
                'agency_id' => $agencyId,
                'reserve_bundle_invoice_id' => $reserveBundleInvoiceId,
            ],
            $with,
            $select,
            $getDeleted
        );
    }

    /**
     * 当該IDレコードを全取得
     *
     * @param array $ids 請求ID配列
     */
    public function getByIds(array $ids, array $with = [], array $select = [], bool $getDeleted = false) : Collection
    {
        return $this->reserveInvoiceRepository->getWhereIn('id', $ids, $with, $select, $getDeleted);
    }

    public function updateOrCreate(array $where, array $data): ReserveInvoice
    {
        return $this->reserveInvoiceRepository->updateOrCreate($where, $data);
    }

    /**
     * 入金額関連カラムを更新
     */
    public function updateDepositAmount(ReserveInvoice $reserveInvoice) : ReserveInvoice
    {
        $this->updateFields(
            $reserveInvoice->id,
            [
                'deposit_amount' => $reserveInvoice->sum_deposit,
                'not_deposit_amount' => $reserveInvoice->sum_not_deposit,
            ]
        );

        return $this->find($reserveInvoice->id);
    }

    /**
     * 新規登録or更新
     */
    public function upsert(int $agencyId, int $reserveId, int $reserveItineraryId, array $input) : ReserveInvoice
    {
        $oldReserveInvoice = $this->findByReserveId($reserveId);

        // 宛先区分が法人でない場合はbusiness_user_idを確実にクリアしておく
        if (Arr::get($input, 'document_address.type') !== config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {
            $input['business_user_id'] = null;
        }

        $input['billing_address_name'] = ''; // 請求先名。検索用に保存
        if (Arr::get($input, 'document_address.type') === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) { // 法人顧客の場合は会社名
            $input['billing_address_name'] = Arr::get($input, 'document_address.company_name');
        } elseif (Arr::get($input, 'document_address.type') === config('consts.reserves.PARTICIPANT_TYPE_PERSON')) { // 個人顧客の場合は個人名
            $input['billing_address_name'] = Arr::get($input, 'document_address.name');
        }

        $result = $this->updateOrCreate(['agency_id' => $agencyId, 'reserve_id' => $reserveId, 'reserve_itinerary_id' => $reserveItineraryId], $input);

        // 入金額の再計算＆更新した最新の請求情報を取得
        $newReserveInvoice = $this->updateDepositAmount($result);
        
        // 一括請求関連処理（作成、リレーション設定等）
        $this->reserveBundleInvoiceRefresh($oldReserveInvoice ? $oldReserveInvoice->reserve_bundle_invoice_id : null, $newReserveInvoice);

        return $newReserveInvoice;
    }

    /**
     * 申込者が変更された際の請求データ、一括請求データの更新処理
     *
     * 以下の3パターンが対象ケース
     * 法人→個人
     * 個人→法人
     * 法人→別法人
     *
     * @param ApplicantInterface $oldApplicant 更新前の申込者情報
     * @param ApplicantInterface $newApplicant 更新後の申込者情報
     * @param ReserveInvoice $currentReserveInvoice 現状の請求レコード
     */
    public function chengedApplicantable(ApplicantInterface $oldApplicant, ApplicantInterface $newApplicant, ReserveInvoice $currentReserveInvoice)
    {
        if ($newApplicant->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {
            if (
                $oldApplicant->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS') || /**** 法人→別法人 *****/
                $oldApplicant->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_PERSON') /**** 個人→法人 *****/
                ) {
                // 現状の請求レコードから一括請求レコードの紐付けを解除し、法人IDを更新
                $this->updateFields($currentReserveInvoice->id, [
                    'business_user_id' => $newApplicant->business_user_id,
                    'reserve_bundle_invoice_id' => null,
                ]);
                // 新しい請求レコードを元に一括請求レコードをリフレッシュ
                $this->reserveBundleInvoiceRefresh($currentReserveInvoice->reserve_bundle_invoice_id, $this->find($currentReserveInvoice->id));
            }
        } elseif ($newApplicant->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_PERSON')) {
            if ($oldApplicant->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) { /**** 法人→個人 *****/
                // 現状の請求レコードから法人顧客と一括請求レコードの紐付けを解除
                $this->updateFields($currentReserveInvoice->id, [
                    'business_user_id' => null,
                    'reserve_bundle_invoice_id' => null,
                ]);
                // 新しい請求レコードを元に一括請求レコードをリフレッシュ
                $this->reserveBundleInvoiceRefresh($currentReserveInvoice->reserve_bundle_invoice_id, $this->find($currentReserveInvoice->id));
            }
        }
    }

    /**
     * ステータス更新
     */
    public function updateStatus(int $reserveInvoiceId, int $status) : bool
    {
        return $this->reserveInvoiceRepository->updateStatus($reserveInvoiceId, $status);
    }

    /**
     * フィールド更新
     */
    public function updateFields(int $reserveInvoiceId, array $params) : bool
    {
        return $this->reserveInvoiceRepository->updateFields($reserveInvoiceId, $params);
    }

    /**
     * 請求番号を生成
     * 接頭辞に予約管理を表す「I」を付ける
     *
     * フォーマット: I西暦下2桁 + 会社識別子 + - + 月 + 3桁連番 + アルファベット
     *
     * @param string $agencyId 会社ID
     * @return string
     */
    public function createInvoiceNumber($agencyId) : string
    {
        $chars = range('A', 'Z');

        // 次の連番を取得
        $seqNumber = $this->reserveInvoiceSequenceService->getNextNumber($agencyId, date('Y-m-d'));

        $ranges = array_chunk(range(1, $seqNumber), 999); // 1000で繰り上がり

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;

        $agency = $this->agencyRepository->find($agencyId);

        return sprintf("I%02d%s-%02d%03d%s", date('y'), $agency->identifier, date('m'), $seq, $chars[$range]);
    }

    ////////// interface

    /**
     * 宛名情報をクリア（宛名情報＆法人顧客ID）
     */
    public function clearDocumentAddress(int $reserveId) : bool
    {
        return $this->reserveInvoiceRepository->clearDocumentAddress($reserveId);
    }
}
