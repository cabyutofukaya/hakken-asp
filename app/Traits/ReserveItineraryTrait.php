<?php

namespace App\Traits;

use App\Models\Participant;
use App\Models\Reserve;
use App\Models\ReservePurchasingSubject;
use Illuminate\Support\Arr;

/**
 * ReserveItineraryのcomposerで使うtrait
 */
trait ReserveItineraryTrait
{
    /**
     * 参加者データを取得
     *
     * @param object $participant
     */
    public function getPaticipantRow(Participant $participant)
    {
        $row = array();
        $row['participant_id'] = $participant->id;
        // 年齢情報はparticipantsテーブルから取得
        $row['age'] = $participant->age;
        $row['age_kbn'] = $participant->age_kbn; // 表示には使用しないが、プログラム内で年齢区分の判定処理に使用するのでセットしておく
        $row['age_kbn_label'] = $participant->age_kbn_label;
        //
        $row['name'] = $participant->name;
        $row['name_kana'] = $participant->name_kana;
        $row['sex_label'] = $participant->sex_label;
        $row['cancel'] = $participant->cancel;
        $row['room_number'] = ""; // 部屋番号は空文字で初期化。カラムをセットしておかないとホテル科目の集計が合わなくなってしまうため
        return $row;
    }

    /**
     * 科目データに関する共通処理（オプション、ホテル、航空）
     *
     * @param ReservePurchasingSubject $subject
     * @param string $subjectCagetory 科目値
     * @param date $date 旅行日
     * @param int $i 行程番号
     * @param int $j 仕入番号
     * @param array $customFields カスタム項目情報
     * @param Collection $participantIds 参加者ID情報
     * @param array $defaultValue form入力値
     */
    private function subjectProcessCommon(ReservePurchasingSubject $subject, string $subjectCagetory, string $date, int $i, int $j, array $customFields, array $participants, $participantIds, &$defaultValue)
    {
        // 当該レコードに設定されたカスタム項目値
        $vReservePurchasingSubjectCustomValues = $subject->subjectable->v_reserve_purchasing_subject_custom_values;
        $userCustomItem = array();
        foreach ($customFields[$subjectCagetory]->pluck('key') as $key) {
            $row = $vReservePurchasingSubjectCustomValues->firstWhere('key', $key);
            $userCustomItem[$key] = Arr::get($row, 'val'); // val値をセット
        }
        ////////////////

        $defaultValue['dates'][$date][$i]['reserve_purchasing_subjects'][$j] = array_merge(
            Arr::except($subject->subjectable->toArray(), ['reserve_participant_prices','v_reserve_purchasing_subject_custom_values'], []),
            $userCustomItem,
            ['mode' => config('consts.reserve_itineraries.PURCHASING_MODE_EDIT'), 'subject' => $subjectCagetory, 'id' => $subject->subjectable ? $subject->subjectable->id : null]
        ); // 仕入科目。保存データ・カスタム項目・定数データ(mode等)を結合して初期化。mode=新規or編集、subject=科目種別

        // participantを一旦空に
        $defaultValue['dates'][$date][$i]['reserve_purchasing_subjects'][$j]['participants'] = [];

        foreach ($subject->subjectable->reserve_participant_prices as $price) {
            if (($k = array_search($price->participant->id, $participantIds->toArray())) !== false) { // キーはparticipantIdsの順番でマッピング
                $defaultValue['dates'][$date][$i]['reserve_purchasing_subjects'][$j]['participants'][$k] = array_merge(
                    Arr::except($price->toArray(), ['participant'], []),
                    ['participant_id' => $price->participant->id],
                    ['age_kbn' => Arr::get($price->toArray(), 'participant.age_kbn')] // 年齢区分はparticipantsに保持されている
                ); // 料金明細
            }
        }

        /**
         * 追加された参加者をチェックして
         * 初期値をセットする
         */
        // diffの引数は当該仕入明細の参加者としてDBに保存されいているID一覧
        $newParticipationIds = $participantIds->diff($subject->subjectable->reserve_participant_prices->map(function ($item) {
            return $item->participant->id;
        }));

        if ($newParticipationIds->isNotEmpty()) { // 追加参加者あり
            foreach ($newParticipationIds->all() as $pid) {
                if (($l = array_search($pid, $participantIds->toArray())) !== false) { // キーはparticipantIdsの順番でマッピング

                    $targetParticipant = collect($participants)->firstWhere('participant_id', $pid);

                    // reserve_itinerary-create-edit.jsのinitialTargetPurchasingと同様
                    $defaultValue['dates'][$date][$i]['reserve_purchasing_subjects'][$j]['participants'][$l] = array_merge(
                        // $subjectCagetory == config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL') ? ['room_number' => null] : [], // ホテル科目の場合はルーム番号も初期化 → getInitialData内で初期化するように変更
                        $this->reserveParticipantOptionPriceService->getInitialData($pid, $targetParticipant['age_kbn'], $subject->subjectable->toArray(), $targetParticipant['cancel'] == 1)
                    ); // 料金明細
                }
            }
        }
    }

    // /**
    //  * accountPayableReservesレコードの新規作成or更新
    //  * 当該予約の支払金額が更新される際に呼び出す
    //  *
    //  * @param Reserve $reserve 予約情報
    //  */
    // public function accountPayableReserveUpdateOrCreate(Reserve $reserve)
    // {
    //     $sumNet = 0;
    //     if ($reserve->enabled_reserve_itinerary->id) { // 有効行程あり
    //         $sumNet = $reserve->enabled_reserve_itinerary->sum_net; // 仕入先への総支払額
    //     }

    //     // 支払管理(予約毎)レコードを新規登録or更新
    //     if (($accountPayableReserve = $this->accountPayableReserveService->findByReserveId($reserve->id))) { // 当該予約の支払管理レコードが既に存在する場合はamount_billedが変更されていたら更新。ステータスと未払金額はChangePaymentReserveAmountEventで更新するので支払額のみ書き換え
    //         if ($accountPayableReserve->amount_billed !== $sumNet) {
    //             $this->accountPayableReserveService->update($accountPayableReserve->id, [
    //                 'amount_billed' => $sumNet,
    //             ]);
    //         }
    //     } else { // 新規作成
                    
    //         $this->accountPayableReserveService->create(
    //             [
    //                 'reserve_id' => $reserve->id,
    //                 'agency_id' => $reserve->agency_id,
    //                 'amount_billed' => $sumNet,
    //                 // 新規登録時はステータス=未払、未払金額=支払額で初期化
    //                 'status' => config('consts.account_payable_reserves.STATUS_UNPAID'),
    //                 'unpaid_balance' => $sumNet,
    //             ]
    //         );
    //     }
    // }
}
