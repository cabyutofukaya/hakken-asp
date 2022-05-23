<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\Reserve;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\WebReserve\WebReserveRepository;
use App\Services\AccountPayableService;
use App\Services\BusinessUserManagerService;
use App\Services\ParticipantService;
use App\Services\ReserveCustomValueService;
use App\Services\ReserveTravelDateService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Services\WebEstimateSequenceService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\WebReserveSequenceService;
use App\Traits\ConstsTrait;
use App\Traits\ReserveTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;


/**
 * reservesテーブルでweb予約用のデータを扱うサービスクラス
 * 
 * Web予約・見積の共通処理をまとめたWebReserveServiceとWebEstimateService親クラス
 */
class WebReserveEstimateService extends ReserveBaseService implements ReserveEstimateInterface
{
    use ConstsTrait, UserCustomItemTrait, ReserveTrait;
    
    public function __construct(
        AccountPayableService $accountPayableService,
        AgencyRepository $agencyRepository,
        BusinessUserManagerService $businessUserManagerService,
        ParticipantService $participantService,
        ReserveCustomValueService $reserveCustomValueService,
        ReserveTravelDateService $reserveTravelDateService,
        UserCustomItemService $userCustomItemService,
        UserService $userService,
        WebEstimateSequenceService $webEstimateSequenceService,
        WebReserveRepository $webReserveRepository,
        ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService,
        ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService,
        ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService,
        WebReserveSequenceService $webReserveSequenceService
    ) {
        $this->accountPayableService = $accountPayableService;
        $this->agencyRepository = $agencyRepository;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->participantService = $participantService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->reserveTravelDateService = $reserveTravelDateService;
        $this->userCustomItemService = $userCustomItemService;
        $this->userService = $userService;
        $this->webEstimateSequenceService = $webEstimateSequenceService;
        $this->webReserveRepository = $webReserveRepository;
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->webReserveSequenceService = $webReserveSequenceService;
    }

    /**
     * 予約IDから予約データを取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false)
    {
        return $this->webReserveRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * 項目更新
     */
    public function updateFields(int $reserveId, array $params) : bool
    {
        return $this->webReserveRepository->updateFields($reserveId, $params);
    }

    /**
     * 更新
     * ReserveEstimateService@updateと同じ処理
     * 
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, string $agencyAccount, array $data) : Reserve
    {
        $old = $this->webReserveRepository->find($id);
        if ($old->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        // 申込区分、顧客番号から申込者情報を取得し保存配列にマージ
        $data = array_merge(
            $data,
            $this->getApplicantCustomerIdInfo($agencyAccount, $data['participant_type'], $data['applicant_user_number'], $this->userService, $this->businessUserManagerService)
        );

        $reserve = $this->webReserveRepository->update($id, $data);

        /**
         * 編集により利用がなくなった旅行日はそれに関連する行程レコード等も削除
         */
        $oldTravelDates = $this->getTravelDates($old, 'Y-m-d');
        $newTravelDates = $this->getTravelDates($reserve, 'Y-m-d');

        if ($deletedDays = array_diff($oldTravelDates, $newTravelDates)) {
            foreach ($reserve->reserve_itineraries as $reserveItinerary) {
                $this->reserveTravelDateService->deleteForItineraryDays($reserveItinerary->id, $deletedDays, true);
            }

            // 当該予約において、買い掛け金明細がなくなったaccount_payablesレコードを削除
            $this->accountPayableService->deleteDoseNotHaveDetails($reserve->id, true);
        }

        
        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->reserveCustomValueService->upsertCustomFileds($customFields, $reserve->id); // カスタムフィールド保存
        }

        return $reserve;
    }

    /**
     * 全参加者を取得
     *
     * @param boolean $getCanceller 取消者も含むか否か
     * @return Illuminate\Support\Collection
     */
    public function getParticipants(int $reserveId, bool $getCanceller = true) : Collection
    {
        return $this->participantService->getByReserveId($reserveId, ['user'], [], $getCanceller);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->webReserveRepository->delete($id, $isSoftDelete);
    }

    /**
     * 予約番号を生成
     * 接頭辞に予約管理を表す「WR」を付ける(WはWEBの意)
     *
     * フォーマット: WR西暦下2桁 + 会社識別子 + - + 月 + 3桁連番 + アルファベット
     *
     * @param string $agencyId 会社ID
     * @return string
     */
    public function createReserveNumber($agencyId) : string
    {
        $chars = range('A', 'Z');

        // 次の連番を取得
        $seqNumber = $this->webReserveSequenceService->getNextNumber($agencyId, date('Y-m-d'));

        $ranges = array_chunk(range(1, $seqNumber), 999); // 1000で繰り上がり

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;

        $agency = $this->agencyRepository->find($agencyId);

        return sprintf("WR%02d%s-%02d%03d%s", date('y'), $agency->identifier, date('m'), $seq, $chars[$range]);
    }
}
