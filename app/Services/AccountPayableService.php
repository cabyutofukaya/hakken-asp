<?php

namespace App\Services;

use App\Models\AccountPayable;
use App\Models\Reserve;
use App\Models\Supplier;
use App\Repositories\AccountPayable\AccountPayableRepository;
use App\Repositories\Agency\AgencyRepository;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AccountPayableService
{
    public function __construct(AgencyRepository $agencyRepository, AccountPayableRepository $accountPayableRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->accountPayableRepository = $accountPayableRepository;
    }

    /**
     * 当該行程管理IDに紐づく買い掛け金データを取得
     */
    public function getByReserveItineraryId(int $reserveItineraryId, array $with=[], array $select=[], bool $getDeleted = false) : Collection
    {
        return $this->accountPayableRepository->getByReserveItineraryId($reserveItineraryId, $with, $select, $getDeleted);
    }

    /**
     * 当該行程における、買掛金明細がなくなった買掛金レコードを削除（親レコード、子レコードとも）
     *
     * @param int $reserveItineraryId 行程ID
     * @param array $supplierIds 仕入先ID一覧。当リストに含まれる仕入先は削除対象外
     * @param bool $isSoftDelete 論理削除の場合はTrue
     */
    public function deleteLostPurchaseData(int $reserveItineraryId, array $supplierIds, bool $isSoftDelete = true) : bool
    {
        return $this->accountPayableRepository->deleteLostPurchaseData($reserveItineraryId, $supplierIds, $isSoftDelete);
    }
    
    /**
     * 当該予約において、買い掛け金明細がなくなったaccount_payablesレコードを削除
     *
     * @param int $reserveId 予約ID
     * @param bool $isSoftDelete 論理削除か否か
     */
    public function deleteDoseNotHaveDetails(int $reserveId, bool $isSoftDelete = true) : bool
    {
        return $this->accountPayableRepository->deleteDoseNotHaveDetails($reserveId, $isSoftDelete);
    }

    /**
     * 支払日を算出
     *
     * @param Supplier $supplier
     * @param Reserve $reserve
     * @return string YYYY-MM-DD形式の文字列
     */
    public function calcPaymentDate(Supplier $supplier, Reserve $reserve)
    {
        // 戻り値初期化
        $res = null;

        // 基準日
        $REFERENCE = data_get($supplier, 'reference_date');
        // 締日
        $CUTOF_DATE = data_get($supplier, 'cutoff_date');
        // 支払日（月）
        $PAYMENT_MONTH = data_get($supplier, 'payment_month');
        // 支払日（日）
        $PAYMENT_DAY = data_get($supplier, 'payment_day');

        if (is_empty($REFERENCE) || is_empty($CUTOF_DATE) || is_empty($PAYMENT_MONTH) || is_empty($PAYMENT_DAY)) { // 一つでも要素が欠けていると日付を計算できないので処理ナシ
            return $res;
        }

        $refDate = null;
        // 基準日
        switch ($REFERENCE) {
            case config('consts.suppliers.REFERENCE_DATE_APPLICATION_DATE'): // 申込日
                if ($reserve->application_dates->isNotEmpty()) {
                    if ($referenceDate = $reserve->application_dates[0]->val) {
                        $refDate = new Carbon($referenceDate);
                    }
                }
                break;
            case config('consts.suppliers.REFERENCE_DATE_DEPARTURE_DATE'): // 出発日
                if ($referenceDate = data_get($reserve, 'departure_date')) {
                    $refDate = new Carbon($referenceDate);
                }
                break;
            case config('consts.suppliers.REFERENCE_DATE_RETURN_DATE'): // 帰着日
                if ($referenceDate = data_get($reserve, 'return_date')) {
                    $refDate = new Carbon($referenceDate);
                }
                break;
            case config('consts.suppliers.REFERENCE_DATE_GUIDANCE_DEADLINE'): // 案内期限
                if ($reserve->guidance_deadlines->isNotEmpty()) {
                    if ($referenceDate = $reserve->guidance_deadlines[0]->val) {
                        $refDate = new Carbon($referenceDate);
                    }
                }
                break;
            case config('consts.suppliers.REFERENCE_DATE_FNL_DATE'): // FNL日
                if ($reserve->fnl_dates->isNotEmpty()) {
                    if ($referenceDate = $reserve->fnl_dates[0]->val) {
                        $refDate = new Carbon($referenceDate);
                    }
                }
                break;
            case config('consts.suppliers.REFERENCE_DATE_TICKETLIMIT'): // ticketlimit
                if ($reserve->ticketlimits->isNotEmpty()) {
                    if ($referenceDate = $reserve->ticketlimits[0]->val) {
                        $refDate = new Carbon($referenceDate);
                    }
                }
                break;
            default:
                break;
    
        }

        if ($refDate) {
            // 対象日が締日以内かを調べる
            if ($refDate->day <= $CUTOF_DATE) { // 基準日が締日以内

                $lmd = Carbon::create($refDate->year, $refDate->month, 1)->lastOfMonth()->day;

                $d = $lmd < $CUTOF_DATE ? $lmd : $CUTOF_DATE;
                
                // 当月で締日を設定
                $cutofDate = Carbon::create($refDate->year, $refDate->month, $d);
            } else { // 基準日が締日を超えている
                //　翌月の締日を求める
                $nextRefDate = $refDate->copy()->addMonthsNoOverflow(1);
                $lmd = $nextRefDate->lastOfMonth()->day; // 当該月の最終日
                $d = $lmd < $CUTOF_DATE ? $lmd : $CUTOF_DATE;
                    
                // 翌月で締日を設定
                $cutofDate = Carbon::create($nextRefDate->year, $nextRefDate->month, $d);
            }
            // 締日を基準に振り込み日を計算
            $payY = $cutofDate->copy()->addMonthsNoOverflow($PAYMENT_MONTH)->year; // 支払年
            $payM = $cutofDate->copy()->addMonthsNoOverflow($PAYMENT_MONTH)->month; // 支払月

            $payLastD = Carbon::create($payY, $payM, 1)->lastOfMonth()->day;
            // 支払日（支払日の設定が当該月末を超えていたら月末に設定）
            $payD = $PAYMENT_DAY >= $payLastD ? $payLastD : $PAYMENT_DAY;

            $res = Carbon::create($payY, $payM, $payD)->format('Y-m-d');
        }

        return $res;
    }

    /**
     * 登録or更新
     *
     * @param int $reserveStatus 予約ステータス(見積or予約)
     */
    public function updateOrCreate(array $where, array $params) : ?AccountPayable
    {
        return $this->accountPayableRepository->updateOrCreate($where, $params);
        
        // if ($reserveStatus == config('consts.reserves.APPLICATION_STEP_DRAFT')) { //見積状態の時は仕入先情報含め更新可
        //     return $this->accountPayableRepository->updateOrCreate($where, $params);
        // } elseif ($reserveStatus == config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約状態の時は更新不可
        //     if ($accountPayable = $this->accountPayableRepository->whereExists($where)) { // 更新
        //         // 予約ステータスの場合は更新ナシ
        //         return $accountPayable;
        //     } else { //新規
        //         return $this->accountPayableRepository->save(array_merge($where, $params, ['supplier_name' => $supplier->name])); // 仕入先名称をセット
        //     }
        // }
        // return null;
    }

    // /**
    //  * 管理番号を生成
    //  *
    //  * フォーマット: 工程表番号 + 仕入先コード
    //  * 仕入先コードに大文字小文字を混ぜられるとまずので、⬇︎に変更
    //  * 工程表番号 + 仕入先IDのハッシュ値
    //  * 予約番号を入れないと一意性が保てなくなるので、予約番号を入れる。そうなると長くなるのでとりあえずmd5形式にする
    //  * 予約ID+行程ID+仕入IDのMD5値(各値の後ろにハイフンを付ける)
    //  *
    //  * @param int $itineraryNumber 工程表番号
    //  * @return string
    //  */
    // public function createUserNumber(int $reserveId, int $reserveItineraryId, int $supplierId) : string
    // {
    //     return md5(sprintf("%d-%d-%d-", $reserveId, $reserveItineraryId, $supplierId));
    // }
}
