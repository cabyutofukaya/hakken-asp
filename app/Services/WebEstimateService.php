<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Reserve;
use App\Models\User;
use App\Traits\ReserveTrait;

/**
 * Web見積処理のサービスクラス。
 * WebReserveEstimateServiceを継承
 */
class WebEstimateService extends WebReserveEstimateService
{
    use ReserveTrait;

    /**
     * 依頼番号から1件取得
     */
    public function findByRequestNumber(string $requestNumber, string $agencyAccount, array $with = [], array $select=[], bool $getDeleted = false) : ?Reserve
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->webReserveRepository->findByRequestNumber(
            $requestNumber,
            $agencyId,
            $with,
            $select,
            $getDeleted
        );
    }

    /**
     * 見積番号から1件取得
     */
    public function findByEstimateNumber(string $estimateNumber, string $agencyAccount, array $with = [], array $select=[], bool $getDeleted = false) : ?Reserve
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->webReserveRepository->findByEstimateNumber(
            $estimateNumber,
            $agencyId,
            $with,
            $select,
            $getDeleted
        );
    }

    /**
     * 一覧を取得
     * スコープは見積状態に設定
     *
     * @param string $account 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);

        return $this->webReserveRepository->paginateByAgencyId(
            $agencyId,
            config('consts.reserves.APPLICATION_STEP_DRAFT'), // 予約確定前状態
            $params,
            $limit,
            $with,
            $select
        );
    }

    /**
     * reservesレコードへの承諾処理(Web相談状態を見積状態に変更)
     *
     * ・見積番号の発行
     * ・申込者情報をセット
     * ・申込段階ステータス(application_step)を見積状態にセット
     * ・参加者に申込者を追加
     * ・カスタム項目の保存処理(ステータス値のデフォルトをセット)
     */
    public function consent(string $agencyAccount, int $reserveId, int $agencyId, User $user) : Reserve
    {
        $data = [];
        
        // 見積番号
        $data['estimate_number'] = $this->createEstimateNumber($agencyId);
        
        //　申込者情報(検索用ポリモーフィックリレーションも同時に作成)
        $data = array_merge(
            $data, 
            $this->getApplicantCustomerIdInfo(
                $agencyAccount, 
                config('consts.reserves.PARTICIPANT_TYPE_PERSON'), 
                $user->user_number,
                $this->userService, 
                $this->businessUserManagerService
        ));

        // 申込段階
        $data['application_step'] = config('consts.reserves.APPLICATION_STEP_DRAFT');
        // レコード番号発行日時を更新(ソートに使用)
        $data['latest_number_issue_at'] = date('Y-m-d H:i:s');

        // 更新
        $this->webReserveRepository->updateFields($reserveId, $data);

        $reserve = $this->webReserveRepository->find($reserveId);

        // 顧客区別が個人の場合は申込者を参加者に追加
        if ($reserve->applicantable_type === 'App\Models\User') {
            $this->participantService->createFromUser($reserve, $agencyId, $reserve->applicantable, true);
        }

        return $reserve;
    }

    /**
     * 見積番号を生成
     * 接頭辞に予約管理を表す「WE」を付ける(WはWEBの意)
     *
     * フォーマット: WE西暦下2桁 + 会社識別子 + - + 月 + 3桁連番 + アルファベット
     *
     * @param string $agencyId 会社ID
     * @return string
     */
    public function createEstimateNumber($agencyId) : string
    {
        $chars = range('A', 'Z');

        // 次の連番を取得
        $seqNumber = $this->webEstimateSequenceService->getNextNumber($agencyId, date('Y-m-d'));

        $ranges = array_chunk(range(1, $seqNumber), 999); // 1000で繰り上がり

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;

        $agency = $this->agencyRepository->find($agencyId);

        return sprintf("WE%02d%s-%02d%03d%s", date('y'), $agency->identifier, date('m'), $seq, $chars[$range]);
    }

}
