<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\Reserve;
use App\Models\ReserveItinerary;
use App\Models\ReserveConfirm;
use App\Models\DocumentQuote;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\ReserveConfirm\ReserveConfirmRepository;
use App\Services\ReserveConfirmBusinessUserManagerService;
use App\Services\ReserveConfirmUserService;
use App\Services\ReserveItineraryService;
use App\Services\DocumentQuoteService;
use App\Services\ReserveService;
use App\Services\EstimateService;
use App\Traits\BusinessFormTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ReserveConfirmService extends ReserveDocumentService implements DocumentAddressInterface
{
    use BusinessFormTrait;

    public function __construct(AgencyRepository $agencyRepository, ReserveConfirmRepository $reserveConfirmRepository, ReserveConfirmUserService $reserveConfirmUserService, ReserveConfirmBusinessUserManagerService $reserveConfirmBusinessUserManagerService, ReserveItineraryService $reserveItineraryService, DocumentQuoteService $documentQuoteService, ReserveService $reserveService, EstimateService $estimateService)
    {
        $this->agencyRepository = $agencyRepository;
        $this->reserveConfirmBusinessUserManagerService = $reserveConfirmBusinessUserManagerService;
        $this->reserveConfirmRepository = $reserveConfirmRepository;
        $this->reserveConfirmUserService = $reserveConfirmUserService;
        $this->reserveItineraryService = $reserveItineraryService;
        $this->documentQuoteService = $documentQuoteService;
        $this->reserveService = $reserveService;
        $this->estimateService = $estimateService;
    }

    /**
     * 予約IDから予約データを取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ?ReserveConfirm
    {
        return $this->reserveConfirmRepository->find($id, $with, $select, $getDeleted);
    }

    // /**
    //  * 確認番号と”見積番号”から確認書類データを1件取得
    //  */
    // public function findByConfirmNumberForEstimate(string $confirmNumber, string $estimateNumber, ?string $itineraryNumber, string $agencyAccount, array $with = [], array $select = []) : ?ReserveConfirm
    // {
    //     $reserve = $this->estimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

    //     $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);

    //     return $this->reserveConfirmRepository->findWhere(['reserve_itinerary_id' => $reserveItinerary->id, 'confirm_number' => $confirmNumber], $with, $select);
    // }

    /**
     * 確認番号と”予約情報”から確認書類データを1件取得
     */
    public function findByConfirmNumberForReserve(string $confirmNumber, Reserve $reserve, ?string $itineraryNumber, array $with = [], array $select = []) : ?ReserveConfirm
    {
        $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);

        return $this->reserveConfirmRepository->findWhere(['reserve_itinerary_id' => $reserveItinerary->id, 'confirm_number' => $confirmNumber], $with, $select);
    }

    // /**
    //  * 確認番号と”予約番号”から確認書類データを1件取得
    //  */
    // public function findByConfirmNumberForReserve(string $confirmNumber, string $reserveNumber, ?string $itineraryNumber, string $agencyAccount, array $with = [], array $select = []) : ?ReserveConfirm
    // {
    //     $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);

    //     $reserveItinerary = $this->reserveItineraryService->findByReserveItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);

    //     return $this->reserveConfirmRepository->findWhere(['reserve_itinerary_id' => $reserveItinerary->id, 'confirm_number' => $confirmNumber], $with, $select);
    // }

    /**
     * 当該行程管理IDに紐づく全予約確認データを取得
     */
    public function getByReserveItineraryId(int $reserveItineraryId, array $with=[], array $select=[]) : Collection
    {
        return $this->reserveConfirmRepository->getByReserveItineraryId($reserveItineraryId, $with, $select);
    }

    /**
     * 当該行程IDに紐づく見積データを取得
     */
    public function getQuoteByReserveItineraryId(int $reserveItineraryId, array $with = [], array $select = [], bool $getDeleted = false) : ?ReserveConfirm
    {
        return $this->reserveConfirmRepository->findByDocumentQuoteCodeByReserveItineraryId($reserveItineraryId, config('consts.document_categories.CODE_QUOTE_DEFAULT'), $with, $select, $getDeleted);
    }

    /**
     * 当該行程IDに紐づく予約確認書データを取得
     */
    public function getReserveConfirmByReserveItineraryId(int $reserveItineraryId, array $with = [], array $select = [], bool $getDeleted = false) : ?ReserveConfirm
    {
        return $this->reserveConfirmRepository->findByDocumentQuoteCodeByReserveItineraryId($reserveItineraryId, config('consts.document_categories.CODE_RESERVE_CONFIRM_DEFAULT'), $with, $select, $getDeleted);
    }

    /**
     * 作成
     */
    public function create(array $data): ReserveConfirm
    {
        if (!Arr::get($data, 'reserve_itinerary_id')) {
            throw new \Exception('no reserve_itinerary_id.');
        }

        // 予約確認番号はレコード数を元に生成するので、createする前の状態であらかじめ発行しておく
        $confirmNumber = $this->createConfirmNumber($data['reserve_itinerary_id']);

        // 予約確認データ保存
        $reserveConfirm = $this->reserveConfirmRepository->create($data);

        // 予約確認場号はfillableによるセット禁止項目なので個別にsave
        $reserveConfirm->confirm_number = $confirmNumber;
        $reserveConfirm->save();

        return $reserveConfirm;
    }

    /**
     * 更新
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $data): ReserveConfirm
    {
        $oldReserveConfirm = $this->find($id);
        if ($oldReserveConfirm->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        // 予約確認データ保存
        return $this->reserveConfirmRepository->update($id, $data);
    }

    /**
     * 予約確認書番号を生成
     *
     * フォーマット: F + 3桁連番（行程管理IDに対する連番）
     *
     * @param int $reserveItineraryId 行程管理ID
     * @return string
     */
    public function createConfirmNumber(int $reserveItineraryId) : string
    {
        // 論理削除も含めた行程管理IDに対するレコード数を集計
        $count = $this->reserveConfirmRepository->getCountByReserveItineraryId($reserveItineraryId, true);

        return sprintf("F-%03d", $count + 1);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->reserveConfirmRepository->delete($id, $isSoftDelete);
    }


    /////////////// 書類作成処理 /////////////

    /**
     * 見積・予約確認作成データ
     * createConfirmFromQuote,createFromReserveItinerary から呼ばれるメソッド
     */
    private function createData(
        int $agencyId, 
        int $reserveId,
        int $reserveItineraryId,
        string $controlNumber, 
        ?string $issueDate,
        ?int $documentQuoteId,
        ?int $documentCommonId,
        array $participantIds, 
        array $documentSetting, 
        array $documentCommonSetting,
        $documentAddress, 
        ?string $name,
        ?string $departureDate,
        ?string $returnDate,
        ?string $manager,
        array $representative,
        ?array $optionPrices, 
        ?array $airticketPrices, 
        ?array $hotelPrices, 
        ?array $hotelInfo, 
        ?array $hotelContacts,
        $status
        )
    {
        // 合計金額を計算
        $amountTotal = get_price_total($participantIds, $optionPrices, $airticketPrices, $hotelPrices);

        return [
            'agency_id' => $agencyId,
            'reserve_id' => $reserveId,
            'reserve_itinerary_id' => $reserveItineraryId,
            'control_number' => $controlNumber,
            'issue_date' => $issueDate,
            'document_quote_id' => $documentQuoteId,
            'document_common_id' => $documentCommonId, 
            'document_address' => $documentAddress,
            'name' => $name,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'manager' => $manager,
            'representative' => $representative,
            'participant_ids' => $participantIds,
            'document_setting' => $documentSetting, // 書類設定
            'document_common_setting' => $documentCommonSetting,
            'option_prices' => $optionPrices,
            'airticket_prices' => $airticketPrices,
            'hotel_prices' => $hotelPrices,
            'hotel_info' => $hotelInfo,
            'hotel_contacts' => $hotelContacts,
            'amount_total' => $amountTotal,
            'status' => $status,
        ];
    }

    /**
     * 見積をもとに予約確認書データを作成
     *
     * @param int $agencyId 会社ID
     * @param ReserveConfirm $quote 見積データ
     * @param ReserveItinerary $reserveItinerary 行程データ
     * @return ReserveConfirm
     */
    public function createConfirmFromQuote(int $agencyId, ReserveConfirm $quote, ReserveItinerary $reserveItinerary) : ReserveConfirm
    {
        // 予約確認書設定データ
        $documentConfirm = $this->documentQuoteService->getDefaultByCode($agencyId, config('consts.document_categories.CODE_RESERVE_CONFIRM_DEFAULT'));

        $controlNumber = data_get($reserveItinerary, 'reserve.control_number'); // 予約番号

        // 書類設定。$documentConfirmが未設定なればsetting配列、sealプロパティ初期化
        $documentSetting = $this->getDocumentSettingSealOrInitSetting($documentConfirm ? $documentConfirm->toArray() : []);

        // 「検印欄」の表示・非表示は設定がイレギュラーにつき、他の設定項目と形式を合わせる
        $this->setSealSetting($documentSetting, config('consts.document_quotes.DISPLAY_BLOCK'));

        // 予約確認データ作成
        return $this->create(
            $this->createData(
                $agencyId,
                $quote->reserve_id,
                $quote->reserve_itinerary_id,
                $controlNumber,
                null,
                $documentConfirm->id ?? null,
                $documentConfirm->document_common_id ?? null,
                $quote->participant_ids,
                $documentSetting,
                $quote ? $quote->document_common->toArray() : [],
                $quote->document_address,
                $quote->name,
                $quote->departure_date,
                $quote->return_date,
                $quote->manager,
                $quote->representative ?? [],
                $quote->option_prices,
                $quote->airticket_prices,
                $quote->hotel_prices,
                $quote->hotel_info,
                $quote->hotel_contacts,
                config('consts.reserve_confirms.STATUS_DEFAULT'), // ステータス
            )
        );
    }

    /**
     * 行程データをもとに見積レコードを作成
     *
     * @param ReserveItinerary $reserveItinerary 行程データ
     * @param Collection $participants 参加者データ
     * @return ReserveConfirm
     */
    public function createFromReserveItinerary(ReserveItinerary $reserveItinerary, Collection $participants) : ReserveConfirm
    {
        $agencyId = $reserveItinerary->agency_id; // 会社ID

        $document = null;
        $controlNumber = null;

        // 見積もり状態と予約状態で異なる値
        if ($reserveItinerary->reserve->application_step === config('consts.reserves.APPLICATION_STEP_DRAFT')) { //見積
            $document = $this->documentQuoteService->getDefaultByCode($agencyId, config('consts.document_categories.CODE_QUOTE_DEFAULT')); // 見積設定

            $controlNumber = data_get($reserveItinerary, 'reserve.estimate_number'); // 見積番号
        } elseif ($reserveItinerary->reserve->application_step === config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約
            $document = $this->documentQuoteService->getDefaultByCode($agencyId, config('consts.document_categories.CODE_RESERVE_CONFIRM_DEFAULT')); // 予約確認書設定

            $controlNumber = data_get($reserveItinerary, 'reserve.control_number'); // 予約番号
        }

        // 有効参加者IDを取得
        $participantIds = $this->getDefaultParticipantCheckIds($participants);

        // オプション価格情報、航空券価格情報、ホテル価格情報、宿泊施設情報、宿泊施設連絡先を取得
        list($optionPrices, $airticketPrices, $hotelPrices, $hotelInfo, $hotelContacts) = $this->getPriceAndHotelInfo($reserveItinerary, $reserveItinerary->reserve->is_canceled);

        // 書類設定。$docuemntQuoteが未設定なればsetting配列、sealプロパティ初期化
        $documentSetting = $this->getDocumentSettingSealOrInitSetting($document ? $document->toArray() : []);

        // 「検印欄」の表示・非表示は設定がイレギュラーにつき、他の設定項目と形式を合わせる
        $this->setSealSetting($documentSetting, config('consts.document_quotes.DISPLAY_BLOCK'));

        return $this->create(
            $this->createData(
                $agencyId,
                $reserveItinerary->reserve_id,
                $reserveItinerary->id,
                $controlNumber,
                null,
                $document->id ?? null,
                $document->document_common_id ?? null,
                $participantIds,
                $documentSetting,
                $document ? $document->document_common->toArray() : [],
                $this->getDocumentAddress($reserveItinerary->reserve->applicantable),
                $reserveItinerary->reserve->name,
                $reserveItinerary->reserve->departure_date,
                $reserveItinerary->reserve->return_date,
                $reserveItinerary->reserve->manager ? $reserveItinerary->reserve->manager->name : null,
                $this->getRepresentativeInfo($reserveItinerary->reserve), //代表者
                $optionPrices,
                $airticketPrices,
                $hotelPrices,
                $hotelInfo,
                $hotelContacts,
                config('consts.reserve_confirms.STATUS_DEFAULT'), // ステータス
            )
        );
    }

    //////////////////////////////////

    ////////// interface

    /**
     * 宛名情報をクリア
     */
    public function clearDocumentAddress(int $reserveId) : bool
    {
        return $this->reserveConfirmRepository->clearDocumentAddress($reserveId);
    }
}
