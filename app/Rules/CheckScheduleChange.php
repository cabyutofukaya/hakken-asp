<?php

namespace App\Rules;

use App\Models\Reserve;
use App\Services\AgencyWithdrawalService;
use App\Traits\ReserveTrait;
use Illuminate\Contracts\Validation\Rule;

class CheckScheduleChange implements Rule
{
    use ReserveTrait;

    private $message = "";

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($departureDate, Reserve $reserve, AgencyWithdrawalService $agencyWithdrawalService)
    {
        $this->reserve = $reserve;
        $this->departureDate = $departureDate;
        $this->agencyWithdrawalService = $agencyWithdrawalService;
    }

    /**
     * 旅行日が既存の日程よりも短くなった場合(出発日が延びた or 帰着日の前倒し)、無くなった日付の中で出金登録がある場合はエラー
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$this->departureDate || !$value) {
            return true;
        } // 出発日と帰着日が両方取得できない場合はチェックしない

        if ($this->reserve->departure_date < $this->departureDate || $this->reserve->return_date > $value) {

          // 日付比較のためPOSTデータから一時的なReserveモデルを作成
            $rsv = new Reserve([
              'departure_date' => $this->departureDate,
              'return_date' => $value
          ]);

          $oldTravelDates = $this->getTravelDates($this->reserve, 'Y/m/d'); // 既存の旅行日一覧
          $newTravelDates = $this->getTravelDates($rsv, 'Y/m/d'); // POSTされた旅行日一覧
          if ($deletedDays = array_diff($oldTravelDates, $newTravelDates)) { // 削除日あり
              // 当該予約IDに紐づく出金レコードの日付一覧を取得（結果リストから「日付を抽出」→「重複を弾く」）
              $withDrawalDateArr = $this->agencyWithdrawalService->getByReserveId($this->reserve->id, ['reserve_travel_date:id,travel_date'], ['id','reserve_travel_date_id'])
              ->pluck('reserve_travel_date.travel_date')->unique()->all();

              $errDateArr = array_intersect($deletedDays, $withDrawalDateArr);
              if ($errDateArr) { // 削除した日付に出金日あり
                  sort($errDateArr);
                  $this->message = "変更した旅行日の中に出金済み仕入情報があるため日程を更新できません(" . implode(",", $errDateArr) . ")。支払管理より当該商品の出金履歴を削除してから変更してください。";
                  return false;
              }
          }
        }
        return true;
    }


    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
