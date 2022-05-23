<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\Reserve;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\Reserve\ReserveRepository;
use App\Services\BusinessUserManagerService;
use App\Services\EstimateSequenceService;
use App\Services\ParticipantService;
use App\Services\ReserveCustomValueService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveSequenceService;
use App\Services\ReserveTravelDateService;
use App\Services\UserService;
use App\Services\BusinessUserService;
use App\Services\UserCustomItemService;
use App\Services\AccountPayableService;
use App\Traits\ConstsTrait;
use App\Traits\UserCustomItemTrait;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Traits\ReserveTrait;

/**
 * 予約・見積の共通処理をまとめたReserveServiceとEstimateService親クラス
 */
class ReserveEstimateService extends ReserveBaseService implements ReserveEstimateInterface
{
    use ConstsTrait, UserCustomItemTrait, ReserveTrait;
    
    public function __construct(
        AgencyRepository $agencyRepository,
        ReserveRepository $reserveRepository,
        UserService $userService,
        BusinessUserManagerService $businessUserManagerService,
        ReserveSequenceService $reserveSequenceService,
        ReserveCustomValueService $reserveCustomValueService,
        ParticipantService $participantService,
        ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService,
        EstimateSequenceService $estimateSequenceService,
        UserCustomItemService $userCustomItemService,
        BusinessUserService $businessUserService,
        ReserveTravelDateService $reserveTravelDateService,
        AccountPayableService $accountPayableService,
        ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService,
        ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService
    ) {
        $this->agencyRepository = $agencyRepository;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->participantService = $participantService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveRepository = $reserveRepository;
        $this->reserveSequenceService = $reserveSequenceService;
        $this->userService = $userService;
        $this->estimateSequenceService = $estimateSequenceService;
        $this->userCustomItemService = $userCustomItemService;
        $this->businessUserService = $businessUserService;
        $this->reserveTravelDateService = $reserveTravelDateService;
        $this->accountPayableService = $accountPayableService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
    }

    /**
     * 予約IDから予約データを取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false)
    {
        return $this->reserveRepository->find($id, $with, $select, $getDeleted);
    }

    // /**
    //  * 予約番号から予約データを1件取得
    //  */
    // public function findByControlNumber(string $controlNumber, string $agencyAccount, array $with = [], array $select=[], bool $getDeleted = false) : ?Reserve
    // {
    //     $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
    //     return $this->reserveRepository->findByControlNumber($controlNumber, $agencyId, $with, $select, $getDeleted);
    // }

    // /**
    //  * 一覧を取得（for 会社アカウント）
    //  *
    //  * @param string $account 会社アカウント
    //  * @param int $limit
    //  * @param array $with
    //  */
    // public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    // {
    //     $agencyId = $this->agencyRepository->getIdByAccount($account);
    //     return $this->reserveRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    // }

    /**
     * 作成
     *
     * @param int $receptionType 受付種別(ASP or WEB)
     * @param string $agencyAccount 会社アカウント
     * @param array $data 入力データ
     * @param string $applicationStep 申込段階(予約or見積)
     * @return App\Models\Reserve
     */
    public function create(int $receptionType, string $agencyAccount, array $data, string $applicationStep) : Reserve
    {
        // 見積 or 予約番号発行
        if ($applicationStep === config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 見積
            $data['estimate_number'] = $this->createEstimateNumber($data['agency_id']); // 見積番号を生成
        } elseif ($applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約
            $data['control_number'] = $this->createReserveNumber($data['agency_id']); // 予約番号を生成
        } else {
            throw new Exception("application_step error.");
        }
        // レコード番号発行日時を更新(ソートに使用)
        $data['latest_number_issue_at'] = date('Y-m-d H:i:s');

        // 申込区分、顧客番号から申込者情報を取得し保存配列にマージ
        $data = array_merge(
            $data,
            $this->getApplicantCustomerIdInfo($agencyAccount, $data['participant_type'], $data['applicant_user_number'], $this->userService, $this->businessUserManagerService)
        );
        
        $data['application_step'] = $applicationStep; // 予約ステータスをセット
        $data['reception_type'] = $receptionType; // 受付種別を設定

        $reserve = $this->reserveRepository->create($data);

        // 顧客区別が"個人"の場合は申込者を参加者に追加
        if ($reserve->applicantable_type === 'App\Models\User') {
            $this->participantService->createFromUser($reserve, $reserve->agency_id, $reserve->applicantable, true);
        }

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->reserveCustomValueService->upsertCustomFileds($customFields, $reserve->id); // カスタムフィールド保存
        }

        return $reserve;
    }

    /**
     * 更新
     * WebReserveEstimateService@updateと同じ処理
     * 
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, string $agencyAccount, array $data) : Reserve
    {
        $old = $this->reserveRepository->find($id);
        if ($old->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        // 申込区分、顧客番号から申込者情報を取得し保存配列にマージ
        $data = array_merge(
            $data,
            $this->getApplicantCustomerIdInfo($agencyAccount, $data['participant_type'], $data['applicant_user_number'], $this->userService, $this->businessUserManagerService)
        );

        $reserve = $this->reserveRepository->update($id, $data);


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
     * 項目更新
     */
    public function updateFields(int $reserveId, array $params) : bool
    {
        return $this->reserveRepository->updateFields($reserveId, $params);
    }

    /**
     * 当該ユーザーの利用履歴一覧を取得
     *
     * @param string $agencyAccount 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByUserNumber(string $agencyAccount, string $userNumber, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        $userId = $this->userService->getIdByUserNumber($userNumber, $agencyId);

        return $this->reserveRepository->paginateByUserId(
            $userId,
            $agencyId,
            $params,
            $limit,
            $with,
            $select
        );
    }

    /**
     * 当該法人顧客の利用履歴一覧を取得
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $userNumber 法人顧客番号
     * @param int $limit
     * @param array $with
     */
    public function paginateByBusinessUserNumber(string $agencyAccount, string $userNumber, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        $businessUserId = $this->businessUserService->getIdByUserNumber($userNumber, $agencyId);

        return $this->reserveRepository->paginateByBusinessUserId(
            $businessUserId,
            $agencyId,
            $params,
            $limit,
            $with,
            $select
        );
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->reserveRepository->delete($id, $isSoftDelete);
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
     * 見積番号を生成
     * 接頭辞に予約管理を表す「E」を付ける
     *
     * フォーマット: E西暦下2桁 + 会社識別子 + - + 月 + 3桁連番 + アルファベット
     *
     * @param string $agencyId 会社ID
     * @return string
     */
    public function createEstimateNumber($agencyId) : string
    {
        $chars = range('A', 'Z');

        // 次の連番を取得
        $seqNumber = $this->estimateSequenceService->getNextNumber($agencyId, date('Y-m-d'));

        $ranges = array_chunk(range(1, $seqNumber), 999); // 1000で繰り上がり

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;

        $agency = $this->agencyRepository->find($agencyId);

        return sprintf("E%02d%s-%02d%03d%s", date('y'), $agency->identifier, date('m'), $seq, $chars[$range]);
    }

    /**
     * 予約番号を生成
     * 接頭辞に予約管理を表す「R」を付ける
     *
     * フォーマット: R西暦下2桁 + 会社識別子 + - + 月 + 3桁連番 + アルファベット
     *
     * @param string $agencyId 会社ID
     * @return string
     */
    public function createReserveNumber($agencyId) : string
    {
        $chars = range('A', 'Z');

        // 次の連番を取得
        $seqNumber = $this->reserveSequenceService->getNextNumber($agencyId, date('Y-m-d'));

        $ranges = array_chunk(range(1, $seqNumber), 999); // 1000で繰り上がり

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;

        $agency = $this->agencyRepository->find($agencyId);

        return sprintf("R%02d%s-%02d%03d%s", date('y'), $agency->identifier, date('m'), $seq, $chars[$range]);
    }
}
