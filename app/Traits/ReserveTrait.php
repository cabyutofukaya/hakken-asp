<?php

namespace App\Traits;

use App\Exceptions\ExclusiveLockException;
use App\Models\Reserve;
use App\Services\BusinessUserManagerService;
use App\Services\ParticipantService;
use App\Services\ReserveCustomValueService;
use App\Services\ReserveEstimateInterface;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * 予約データを扱うtrait
 */
trait ReserveTrait
{
    /**
     * 顧客種別と顧客番号から
     * 申込者顧客ID情報を取得
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $customerType 顧客種別
     * @param string $userNumber 顧客番号
     * @return array カラム名 => id 形式の配列
     */
    public function getApplicantCustomerIdInfo(string $agencyAccount, string $customerType, string $userNumber, UserService $userService, BusinessUserManagerService $businessUserManagerService)
    {
        if ($customerType === config('consts.reserves.PARTICIPANT_TYPE_PERSON')) { // 個人顧客
            $user = $userService->findByUserNumber($userNumber, $agencyAccount, [], [], true); // 論理削除も含めて取得

            return 
                [
                    'applicantable_type' => get_class($user),
                    'applicantable_id' => $user->id,
                    // ↑とは別に検索用として具体的なUserレコードを記録
                    'applicant_searchable_type' => get_class($user->userable),
                    'applicant_searchable_id' => $user->userable->id
                ];
        } elseif ($customerType === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) { // 法人顧客
            $businessUser = $businessUserManagerService->findByUserNumber($userNumber, $agencyAccount, [], ['id'], true); // 論理削除も含めて取得

            return 
                [
                    'applicantable_type' => get_class($businessUser),
                    'applicantable_id' => $businessUser->id,
                    // ↑とは別に検索用として具体的なUserレコードを記録
                    'applicant_searchable_type' => get_class($businessUser),
                    'applicant_searchable_id' => $businessUser->id
                ];
        }
        return [];
    }

    /**
     * 旅行日程日にち一覧を取得
     */
    public function getTravelDates(Reserve $reserve, string $format = 'Y/m/d') : array
    {
        $dates = []; // 旅行日一覧を格納

        if ($reserve->departure_date || $reserve->return_date) {
            if ($reserve->departure_date && $reserve->return_date) {
                $s = new Carbon($reserve->departure_date);

                $diff = $s->diffInDays(new Carbon($reserve->return_date));
                for ($i=0; $i<=$diff; $i++) {
                    $dates[] = $s->copy()->addDays($i)->format($format);
                }
            } else { // 出発日or帰着日のどちらか一方が設定
                $dates[] = $reserve->departure_date ? date($format, strtotime($reserve->departure_date)) : date($format, strtotime($reserve->return_date));
            }
        }

        return $dates;
    }

    /**
     * 予約情報から参加者の紐付け解除
     *
     * @param int $reserveId 予約情報
     * @param int $participantId 参加者ID
     * @param bool $isSoftDelete 参加者を論理削除するか否か
     */
    public function detachParticipant(
        int $reserveId,
        int $participantId,
        ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService,
        ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService,
        ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService,
        ParticipantService $participantService,
        bool $isSoftDelete = true
    ) {
        /**
         * [処理の流れ]
         *
         * ① 料金レコードのvalidフラグを無効化
         * ↓
         * ② (出金登録がなければ)料金レコード削除
         * ↓
         * ③ 参加者データ削除
         */

        // ① 当該参加者に関連する料金レコードのvalidフラグを念の為、無効化（仕入オプション科目、仕入航空券科目、仕入ホテル科目）
        $reserveParticipantOptionPriceService->updateValidForParticipant($participantId, false); // オプション科目
        $reserveParticipantAirplanePriceService->updateValidForParticipant($participantId, false); // 航空券
        $reserveParticipantHotelPriceService->updateValidForParticipant($participantId, false); // ホテル

        // ② 当該参加者に関連する料金レコード削除（仕入オプション科目、仕入航空券科目、仕入ホテル科目）。論理削除
        $reserveParticipantOptionPriceService->deleteByParticipantId($participantId, false, true); // オプション科目
        $reserveParticipantAirplanePriceService->deleteByParticipantId($participantId, false, true); // 航空券
        $reserveParticipantHotelPriceService->deleteByParticipantId($participantId, false, true); // ホテル

        // ③ 参加者データ削除
        $participantService->delete($participantId, $isSoftDelete);
        
        return true;
    }

    /**
     * 見積決定処理
     *
     * @param Reserve $estimate 見積データ
     * @param array $data 入力データ
     * @return App\Models\Reserve
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function determine(
        Reserve $estimate,
        array $data,
        UserCustomItemService $userCustomItemService,
        ReserveCustomValueService $reserveCustomValueService,
        ReserveEstimateInterface $reserveEstimateInterface
    ) : bool {
        if ($estimate->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        // 「予約ステータス」のカスタム項目を取得
        $reserveCustomStatus = $userCustomItemService->findByCodeForAgency($estimate->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'));


        // 予約ステータス値をセット(カスタム項目)
        $reserveCustomValueService->create([
            'reserve_id' => $estimate->id,
            'user_custom_item_id' => $reserveCustomStatus->id,
            'val' => config('consts.reserves.RESERVE_DEFAULT_STATUS'), // 手配中
        ]);

        // 申し込み段階を「見積」→「予約」に変更
        $reserveEstimateInterface->updateFields($estimate->id, [
            'application_step' => config('consts.reserves.APPLICATION_STEP_RESERVE'),
            'control_number' => $reserveEstimateInterface->createReserveNumber($estimate->agency_id),
            'latest_number_issue_at' => date('Y-m-d H:i:s'),// レコード番号発行日時を更新(ソートに使用)
        ]);

        return true;
    }
}
