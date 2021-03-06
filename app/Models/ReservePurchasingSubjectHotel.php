<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use App\Traits\ChargeKbnTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HashidsTrait;

class ReservePurchasingSubjectHotel extends Model
{
    use ModelLogTrait,SoftDeletes,SoftCascadeTrait,HashidsTrait,ChargeKbnTrait;

    // 出金登録があるレコードは削除しないように制御したいのでbootメソッドないで処理
    // protected $softCascade = [
    //     'reserve_participant_prices'
    // ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'reserve_schedule_id',
        'code',
        'name',
        'name_ex',
        'supplier_id',
        'hotel_name',
        'address',
        'tel',
        'fax',
        'url',
        'ad_gross_ex',
        'ad_gross',
        'ad_cost',
        'ad_commission_rate',
        'ad_net',
        'ad_zei_kbn',
        'ad_gross_profit',
        'ch_gross_ex',
        'ch_gross',
        'ch_cost',
        'ch_commission_rate',
        'ch_net',
        'ch_zei_kbn',
        'ch_gross_profit',
        'inf_gross_ex',
        'inf_gross',
        'inf_cost',
        'inf_commission_rate',
        'inf_net',
        'inf_zei_kbn',
        'inf_gross_profit',
        'note',
    ];

    protected $guarded = [
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'agency_id',
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'ad_gross_ex' => 'integer',
        'ad_gross' => 'integer',
        'ad_cost' => 'integer',
        'ad_commission_rate' => 'integer',
        'ad_net' => 'integer',
        'ad_gross_profit' => 'integer',
        'ch_gross_ex' => 'integer',
        'ch_gross' => 'integer',
        'ch_cost' => 'integer',
        'ch_commission_rate' => 'integer',
        'ch_net' => 'integer',
        'ch_gross_profit' => 'integer',
        'inf_gross_ex' => 'integer',
        'inf_gross' => 'integer',
        'inf_cost' => 'integer',
        'inf_commission_rate' => 'integer',
        'inf_net' => 'integer',
        'inf_gross_profit' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();

        static::deleting(function ($row) {

            /****** ReserveParticipantHotelPriceに紐づく支払情報について、出金登録がなければreserve_participant_hotel_prices、account_payable_detailsレコードをまとめて削除 ******/
            
            $deleteReserveParticipantHotelPriceIds = []; // 削除対象のdelete_reserve_participant_hotel_pricesのID一覧
            $deleteAccountPayableDetailIds = []; // 削除対象のdelete_account_payable_detailsのID一覧

            \App\Models\ReserveParticipantHotelPrice::with(['account_payable_detail:id,saleable_type,saleable_id'])->select(['id'])->where('reserve_purchasing_subject_hotel_id', $row->id)->doesntHave('account_payable_detail.agency_withdrawals')->chunk(300, function ($rows) use (&$deleteReserveParticipantHotelPriceIds, &$deleteAccountPayableDetailIds) {
                foreach ($rows as $row) {
                    $deleteReserveParticipantHotelPriceIds[] = $row->id;
                    $deleteAccountPayableDetailIds[] = $row->account_payable_detail->id;
                }
            }); // 念の為chunkで少しずつ取得

            if ($deleteReserveParticipantHotelPriceIds) {
                foreach (array_chunk($deleteReserveParticipantHotelPriceIds, 1000) as $ids) { // レコード削除処理。念の為1000件ずつ
                    \App\Models\ReserveParticipantHotelPrice::whereIn('id', $ids)->delete();
                }
            }

            if ($deleteAccountPayableDetailIds) {
                foreach (array_chunk($deleteAccountPayableDetailIds, 1000) as $ids) { // レコード削除処理。念の為1000件ずつ
                    \App\Models\AccountPayableDetail::whereIn('id', $ids)->delete();
                }
            }



            // foreach ($row->reserve_participant_prices as $price) {
            //     if ($price->account_payable_detail && $price->account_payable_detail->agency_withdrawals->isEmpty()) { // 出金登録がなければ削除
            //         $price->delete();
            //     } else { // 出金登録がある場合はvalidフラグを無効に
            //         $price->valid = false;
            //         $price->save();
            //     }
            // }
        });

    }

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    // 仕入科目のポリモーフィックリレーション
    public function reserve_purchasing_subject()
    {
        return $this->morphOne('App\Models\ReservePurchasingSubject', 'subjectable');
    }

    // 旅行スケジュール
    public function reserve_schedule()
    {
        return $this->belongsTo('App\Models\ReserveSchedule')->withDefault();
    }

    // 仕入先
    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier')->withDefault()->withTrashed(); // 削除済みも取得
    }

    // 参加者料金
    public function reserve_participant_prices()
    {
        return $this->hasMany('App\Models\ReserveParticipantHotelPrice');
    }

    // 参加者料金(有効ステータスのみ)
    public function enabled_reserve_participant_prices()
    {
        return $this->hasMany('App\Models\ReserveParticipantHotelPrice')->where('valid', true);
    }

    ///////////// カスタム項目関連 ///////////////

    /**
     * カスタム項目を全取得（有効な項目のみ flg=1）
     */
    public function v_reserve_purchasing_subject_custom_values()
    {
        return $this->hasMany('App\Models\VReservePurchasingSubjectHotelCustomValue')->where('flg', true);
    }

    /**
     * 部屋タイプ（有効な項目のみ対象 flg=1）
     */
    public function room_types()
    {
        return $this->hasMany('App\Models\VReservePurchasingSubjectHotelCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_SUBJECT_HOTEL_ROOM_TYPE'))
            ->where('flg', true);
    }

    /////////////////////////////////////

    // /**
    //  * name_ex(AsyncSelect用)
    //  */
    // public function getNameExAttribute($value): ?object
    // {
    //     return $value ? json_decode($value) : null;
    // }

}
