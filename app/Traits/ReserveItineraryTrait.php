<?php

namespace App\Traits;

use App\Models\Participant;

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
}
