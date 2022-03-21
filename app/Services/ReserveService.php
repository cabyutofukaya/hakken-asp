<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\Reserve;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

// use Illuminate\Support\Collection;

/**
 * ReserveEstimateServiceを継承
 */
class ReserveService extends ReserveEstimateService
{
    /**
     * 予約番号から予約データを1件取得
     */
    public function findByControlNumber(string $controlNumber, string $agencyAccount, array $with = [], array $select=[], bool $getDeleted = false) : ?Reserve
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->reserveRepository->findByControlNumber(
            $controlNumber,
            $agencyId,
            $with,
            $select,
            $getDeleted
        );
    }

    /**
     * 一覧を取得
     * スコープは予約状態に設定
     *
     * @param string $account 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);

        return $this->reserveRepository->paginateByAgencyId(
            $agencyId,
            config('consts.reserves.APPLICATION_STEP_RESERVE'), // 予約状態
            $params,
            $limit,
            $with,
            $select
        );
    }

    // /**
    //  * 作成
    //  *
    //  * @param string $agencyAccount 会社アカウント
    //  * @param array $data 入力データ
    //  * @param string $applicationStep 申込段階(予約or見積)
    //  * @return App\Models\Reserve
    //  */
    // public function create(string $agencyAccount, array $data, string $applicationStep) : Reserve
    // {
    //     if ($applicationStep === config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 見積
    //         $data['estimate_number'] = $this->createEstimateNumber($data['agency_id']); // 見積番号を生成
    //     } elseif ($applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約
    //         $data['control_number'] = $this->createReserveNumber($data['agency_id']); // 予約番号を生成
    //     } else {
    //         throw new Exception("application_step error.");
    //     }

    //     // 申込区分、顧客番号から申込者情報を取得し保存配列にマージ
    //     $data = array_merge(
    //         $data,
    //         $this->getApplicantCustomerIdInfo($agencyAccount, $data['participant_type'], $data['applicant_user_number'])
    //     );

    //     $reserve = $this->reserveRepository->create($data);
        
    //     $reserve->application_step = $applicationStep; // 予約ステータスをセット
    //     $reserve->save();

    //     // 顧客区別が"個人"の場合は申込者を参加者に追加
    //     if ($reserve->applicantable_type === 'App\Models\User') {
    //         $this->participantService->createFromUser($reserve, $reserve->agency_id, $reserve->applicantable, true);
    //     }

    //     $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
    //     if ($customFields) {
    //         $this->reserveCustomValueService->upsertCustomFileds($customFields, $reserve->id); // カスタムフィールド保存
    //     }

    //     return $reserve;
    // }

    // /**
    //  * 更新
    //  */
    // public function update(int $id, string $agencyAccount, array $data) : Reserve
    // {
    //     // 申込区分、顧客番号から申込者情報を取得し保存配列にマージ
    //     $data = array_merge(
    //         $data,
    //         $this->getApplicantCustomerIdInfo($agencyAccount, $data['participant_type'], $data['applicant_user_number'])
    //     );

    //     $reserve = $this->reserveRepository->update($id, $data);

    //     // 顧客区別が"個人"かつ、未参加者の場合は申込者を参加者に追加
    //     if ($reserve->applicantable_type === 'App\Models\User' && !$this->participantService->isExistsInReserve($reserve->applicantable_id, $reserve->id)) {
    //         $this->participantService->createFromUser($reserve, $reserve->agency_id, $reserve->applicantable, false); // 代表者フラグはOFF
    //     }

    //     $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
    //     if ($customFields) {
    //         $this->reserveCustomValueService->upsertCustomFileds($customFields, $reserve->id); // カスタムフィールド保存
    //     }

    //     return $reserve;
    // }

    // /**
    //  * 削除
    //  *
    //  * @param int $id ID
    //  * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
    //  */
    // public function delete(int $id, bool $isSoftDelete=true): bool
    // {
    //     return $this->reserveRepository->delete($id, $isSoftDelete);
    // }

    // /**
    //  * 予約情報から参加者の紐付け解除
    //  *
    //  * @param int $reserveId 予約情報
    //  * @param int $participantId 参加者ID
    //  * @param bool $isSoftDelete 参加者を論理削除するか否か
    //  */
    // public function detachParticipant(int $reserveId, int $participantId, bool $isSoftDelete = true)
    // {
    //     // TODO 182〜189行目を一旦、コメントアウト。動作が問題ないかしばらく様子見

    //     // // Many to Many のリレーション解除 → 参加者データの削除

    //     // $this->reserveRepository->detachParticipant($reserveId, $participantId);

    //     // // 当該参加者に関連する料金レコード削除（仕入オプション科目、仕入航空券科目、仕入ホテル科目）
    //     // // TODO 仕入オプション科目以外は後ほど実装

    //     // $this->reserveParticipantOptionPriceService->deleteByParticipantId($participantId, false); // 物理削除

    //     $this->participantService->delete($participantId, $isSoftDelete); // 参加者データ削除
        
    //     return true;
    // }

    /**
     * 予約キャンセル
     *
     * @param Reserve $reserve 予約情報
     * @param bool $cancelCharge キャンセルチャージの有無
     * @return boolean
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function cancel(Reserve $reserve, bool $cancelCharge, ?string $updatedAt) : bool
    {
        if ($updatedAt && $reserve->updated_at != $updatedAt) {
            throw new ExclusiveLockException;
        }

        if (!$reserve->cancel_at) { // cancel_atカラムの値をセットするのは初回のみ
            return $this->reserveRepository->updateFields($reserve->id, [
                'cancel_at' => date('Y-m-d H:i:s'),
                'cancel_charge' => $cancelCharge
            ]);
        } else {
            return $this->reserveRepository->updateFields($reserve->id, [
                'cancel_charge' => $cancelCharge
            ]);
        }
    }
}
