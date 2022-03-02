<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HashidsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

/**
 * 見積・予約管理テーブル
 * HAKKENの見積・予約も供用
 */
class Reserve extends Model
{
    use SoftDeletes,Sortable,ModelLogTrait,SoftCascadeTrait,HashidsTrait;

    // TODO クエリ実行時、毎回withが動いてしまうので一旦コメント。
    // コメントにしまって大丈夫か確認
    // // 金額集計に使用
    // protected $with = [
    //     'account_payable_details',
    //     'agency_withdrawals',
    //     'agency_deposits',
    // ]; // 請求金額・出金額

    protected $appends = [
        'sum_invoice_amount',
        'sum_withdrawal',
        'sum_unpaid',
        'sum_deposit',
        'sum_not_deposit',
        'hash_id', // ハッシュID
        'is_departed', // 催行済みか否か
    ];
    
    protected $softCascade = [
        'agency_consultations', // 当該予約を消したら相談履歴も削除する
        'web_message_histories', // 当該予約を消したらメッセージ履歴も削除する
        // 'reserve_itineraries',
        // 'account_payables', // 不要?
        // 'account_payable_details', // 不要?
    ];

    // TODO 申込者ソートはなくしても良いか？
    public $sortable = [
        'id',
        'reserve_estimate_number',
        'estimate_reqeust_number',
        'estimate_number',
        'control_number',
        'status',
        'estimate_status',
        'reserve_estimate_status',
        'manager.name',
        'departure_date',
        'return_date',
        'departure.name',
        'destination.name',
        'name',
        'travel_type',
        'application_date',
        'representative_name',
        'headcount',
        'sum_gross',
        'web_reserve_ext.agency_unread_count',
        'web_online_schedule.consult_date',
        'latest_number_issue_at',
        'web_online_schedule',
        'application_type',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'estimate_number',
        'control_number',
        'agency_id',
        'applicantable_type',
        'applicantable_id',
        'applicant_searchable_type', // 検索用
        'applicant_searchable_id', // 検索用
        'name',
        'travel_type',
        'departure_date',
        'return_date',
        'departure_id',
        'departure_place',
        'destination_id',
        'destination_place',
        'note',
        'manager_id',
        'application_step',
        'reception_type',
        'representative_name', // 基本的にはソートに使うだけ。実際の名前の表示はリレーション先の値を使用
        'headcount',
        'sum_gross',
        'sum_withdrawal',
        'latest_number_issue_at',
        'cancel_charge',
    ];

    protected $guarded = [
        'updated_at',
        'cancel_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'headcount' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    // Web予約情報
    public function web_reserve_ext()
    {
        return $this->hasOne('App\Models\WebReserveExt')->withDefault();
    }

    /**
     * 申込者
     */
    public function applicantable()
    {
        return $this->morphTo()->withTrashed(); // 削除済みも取得
    }

    /**
     * 申込者(検索用)
     *
     * 個人顧客のapplicantableが2段階構造(users → asp_users/web_users)になっており
     * 検索処理が難しいので、予約レコードからasp_users or web_usersを直接参照できるように簡略化したリレーション
     */
    public function applicant_searchable()
    {
        return $this->morphTo()->withTrashed(); // 削除済みも取得
    }

    // 参加者（取消者含む）
    public function participants()
    {
        return $this->belongsToMany('App\Models\Participant');
    }

    // 参加者（取消者除く）
    public function participant_except_cancellers()
    {
        return $this->belongsToMany('App\Models\Participant')->where('cancel', false);
    }

    // 代表者
    public function representatives()
    {
        return $this->belongsToMany('App\Models\Participant')->where('representative', true);
    }

    // 出発地
    public function departure()
    {
        return $this->belongsTo('App\Models\VArea', 'departure_id', 'uuid')->withDefault();
    }

    // 目的地
    public function destination()
    {
        return $this->belongsTo('App\Models\VArea', 'destination_id', 'uuid')->withDefault();
    }
    
    // 旅程一覧
    public function reserve_itineraries()
    {
        return $this->hasMany('App\Models\ReserveItinerary');
    }

    // 有効な旅程
    public function enabled_reserve_itinerary()
    {
        return $this->hasOne('App\Models\ReserveItinerary')->where('enabled', true)->withDefault();
    }

    /**
     * 自社担当
     * 論理削除も取得
     */
    public function manager()
    {
        return $this->belongsTo('App\Models\Staff', 'manager_id')
            ->withTrashed()
            ->withDefault();
    }

    // 仕入れ先買掛金
    public function account_payables()
    {
        return $this->hasMany('App\Models\AccountPayable');
    }
    
    // 相談
    public function agency_consultations()
    {
        return $this->hasMany('App\Models\AgencyConsultation');
    }

    // メッセージ履歴
    public function web_message_histories()
    {
        return $this->hasMany('App\Models\WebMessageHistory');
    }

    // 買い掛け金詳細
    public function account_payable_details()
    {
        return $this->hasMany('App\Models\AccountPayableDetail');
    }

    // 有効な買い掛け金詳細
    public function enabled_account_payable_details()
    {
        // 仕入が有効になっているレコードのみ対象に(AccountPayableDetailのscopeIsValidメソッドと同じ処理)
        return $this->hasMany('App\Models\AccountPayableDetail')->whereHasMorph('saleable', [
            'App\Models\ReserveParticipantOptionPrice',
            'App\Models\ReserveParticipantAirplanePrice',
            'App\Models\ReserveParticipantHotelPrice',
        ], function ($q) {
            $q->where('valid', true);
        });
    }

    // 出金詳細
    public function agency_withdrawals()
    {
        return $this->hasMany('App\Models\AgencyWithdrawal');
    }

    // 入金詳細
    public function agency_deposits()
    {
        return $this->hasMany('App\Models\AgencyDeposit');
    }

    /**
     * 請求書一覧
     */
    public function reserve_invoices()
    {
        return $this->hasMany('App\Models\ReserveInvoice');
    }

    /**
     * カスタム項目を全取得（有効な項目のみ flg=1）
     */
    public function v_reserve_custom_values()
    {
        return $this->hasMany('App\Models\VReserveCustomValue')->where('flg', true);
    }

    ////////////////// カスタム項目 ここから ////////////////////

    /**
     * 旅行種別を取得（有効な項目のみ対象 flg=1）
     * travel_typeを残して本メソッドは削除予定
     */
    public function travel_types()
    {
        return $this->hasMany('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_TRAVEL_TYPE'))
            ->where('flg', true);
    }

    /**
     * 旅行種別を取得（有効な項目のみ対象 flg=1）
     */
    public function travel_type()
    {
        return $this->hasOne('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_TRAVEL_TYPE'))
            ->where('flg', true);
    }

    /**
     * ステータスを取得（有効な項目のみ対象 flg=1）
     * TODO このメソッドは削除予定(statusへ移行)
     */
    public function statuses()
    {
        return $this->hasMany('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'))
            ->where('flg', true);
    }

    /**
     * ステータスを取得（hasOne）
     */
    public function status()
    {
        return $this->hasOne('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'))
            ->where('flg', true)->withDefault();
    }

    /**
     * 見積ステータスを取得（有効な項目のみ対象 flg=1）
     * TODO このメソッドは削除予定(estimate_statusへ移行)
     */
    public function estimate_statuses()
    {
        return $this->hasMany('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS'))
            ->where('flg', true);
    }

    /**
     * 見積ステータスを取得(hasOne)
     */
    public function estimate_status()
    {
        return $this->hasOne('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS'))
            ->where('flg', true)->withDefault();
    }
    /**
     * 申込種別を取得（有効な項目のみ対象 flg=1）
     */
    public function application_type()
    {
        return $this->hasOne('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_TYPE'))
            ->where('flg', true);
    }

    /**
     * 申込日を取得（有効な項目のみ対象 flg=1）
     * 本メソッドは削除してapplication_dateに入替予定
     */
    public function application_dates()
    {
        return $this->hasMany('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_APPLICATION_DATE'))
            ->where('flg', true);
    }

    /**
     * 申込日を取得（有効な項目のみ対象 flg=1）
     */
    public function application_date()
    {
        return $this->hasOne('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_APPLICATION_DATE'))
            ->where('flg', true);
    }

    /**
     * 案内期限を取得（有効な項目のみ対象 flg=1）
     */
    public function guidance_deadlines()
    {
        return $this->hasMany('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_GUIDANCE_DEADLINE'))
            ->where('flg', true);
    }

    /**
     * FNL日を取得（有効な項目のみ対象 flg=1）
     */
    public function fnl_dates()
    {
        return $this->hasMany('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_FNL_DATE'))
            ->where('flg', true);
    }

    /**
     * ticketlimit日を取得（有効な項目のみ対象 flg=1）
     */
    public function ticketlimits()
    {
        return $this->hasMany('App\Models\VReserveCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_APPLICATION_TICKETLIMIT'))
            ->where('flg', true);
    }

    ////////////////// カスタム項目 ここまで ////////////////////


    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////

    // ハッシュID
    public function getHashIdAttribute($value) : string
    {
        return $this->getRouteKey();
    }

    /**
     * 催行済みの場合はtrue
     *
     * この条件を変える場合は 「scopeDeparted」 も変更のこと
     */
    public function getIsDepartedAttribute($value) : bool
    {
        return $this->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE') && is_null($this->cancel_at) && date('Y-m-d', strtotime($this->return_date)) < date('Y-m-d');
    }

    // 見積/予約/依頼番号
    public function getRecordNumberAttribute($value): ?string
    {
        if ($this->control_number) {
            return $this->control_number;
        }
        if ($this->estimate_number) {
            return $this->estimate_number;
        }
        if ($this->request_number) {
            return $this->request_number;
        }
        return null;
    }

    // ステータスラベル（見積or予約でリレーションを切り替え）
    public function getStatusLabelAttribute($value) : ?string
    {
        if ($this->application_step == config('consts.reserves.APPLICATION_STEP_DRAFT')) {
            return optional($this->estimate_status)->val;
        } elseif ($this->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE')) {
            return optional($this->status)->val;
        } else {
            return null;
        }
    }

    // 出発日（日付は「YYYY/MM/DD」形式に変換）
    public function getDepartureDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 帰着日（日付は「YYYY/MM/DD」形式に変換）
    public function getReturnDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    /**
     * ツアー名
     *
     * 論理削除レコードの場合は「(削除)」を表記
     */
    public function getNameAttribute($value): ?string
    {
        if ($value) {
            return $this->trashed() ? sprintf("%s(削除)", $value) : $value;
        }
        return null;
    }

    /**
     * 予約番号
     *
     * 論理削除レコードの場合は「(削除)」を表記
     */
    public function getControlNumberAttribute($value): ?string
    {
        if ($value) {
            return $this->trashed() ? sprintf("%s(削除)", $value) : $value;
        }
        return null;
    }

    ///////////////// 読みやすい文字列に変換するAttribute ここまで //////////////

    ///////////////// カスタムソート ここから /////////////////

    /**
     * 予約番号・見積番号ソートメソッド
     */
    public function reserveEstimateNumberSortable($query, $direction)
    {
        return $query->orderBy('control_number', $direction)->orderBy('estimate_number', $direction);
    }

    /**
     * 依頼番号・見積番号ソートメソッド
     */
    public function estimateReqeustNumberSortable($query, $direction)
    {
        return $query->orderBy('estimate_number', $direction)->orderBy('request_number', $direction);
    }

    /**
     * 予約ステータスソートメソッド
     */
    public function statusSortable($query, $direction)
    {
        return $query->select('reserves.*')
            ->leftJoin('v_reserve_custom_values', function ($join) {
                $join->on('reserves.id', '=', 'v_reserve_custom_values.reserve_id')
                    ->where('v_reserve_custom_values.code', config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'))
                    ->where('v_reserve_custom_values.flg', true);
            })->orderBy('v_reserve_custom_values.val', $direction);
    }

    /**
     * 見積ステータスソートメソッド
     */
    public function estimateStatusSortable($query, $direction)
    {
        return $query->select('reserves.*')
            ->leftJoin('v_reserve_custom_values', function ($join) {
                $join->on('reserves.id', '=', 'v_reserve_custom_values.reserve_id')
                    ->where('v_reserve_custom_values.code', config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS'))
                    ->where('v_reserve_custom_values.flg', true);
            })->orderBy('v_reserve_custom_values.val', $direction);
    }

    /**
     * 旅行種別ソートメソッド
     */
    public function travelTypeSortable($query, $direction)
    {
        return $query->select('reserves.*')
        ->leftJoin('v_reserve_custom_values', function ($join) {
            $join->on('reserves.id', '=', 'v_reserve_custom_values.reserve_id')
                ->where('v_reserve_custom_values.code', config('consts.user_custom_items.CODE_APPLICATION_TRAVEL_TYPE'))
                ->where('v_reserve_custom_values.flg', true);
        })->orderBy('v_reserve_custom_values.val', $direction);
    }

    /**
     * 申込日ソートメソッド
     */
    public function applicationDateSortable($query, $direction)
    {
        return $query->select('reserves.*')
            ->leftJoin('v_reserve_custom_values', function ($join) {
                $join->on('reserves.id', '=', 'v_reserve_custom_values.reserve_id')
                    ->where('v_reserve_custom_values.code', config('consts.user_custom_items.CODE_APPLICATION_APPLICATION_DATE'))
                    ->where('v_reserve_custom_values.flg', true);
            })->orderBy('v_reserve_custom_values.val', $direction);
    }

    /**
     * 申し込み種別ソートメソッド
     */
    public function applicationTypeSortable($query, $direction)
    {
        return $query->select('reserves.*')
            ->leftJoin('v_reserve_custom_values', function ($join) {
                $join->on('reserves.id', '=', 'v_reserve_custom_values.reserve_id')
                    ->where('v_reserve_custom_values.code', config('consts.user_custom_items.CODE_APPLICATION_TYPE'))
                    ->where('v_reserve_custom_values.flg', true);
            })->orderBy('v_reserve_custom_values.val', $direction);
    }

    /**
     * オンラインスケジュールソートメソッド
     */
    public function webOnlineScheduleSortable($query, $direction)
    {
        return $query->select('reserves.*')
            ->leftJoin('web_online_schedules', function ($join) {
                $join->on('reserves.id', '=', 'web_online_schedules.reserve_id')->whereNull('web_online_schedules.deleted_at');
            })->orderBy('web_online_schedules.consult_date', $direction);
    }

    /**
     * 予約/見積ステータスソートメソッド
     * 顧客ページの履歴タブで使用 -> 実装難易度が高いので一旦停止。予約と見積もりの両方のステータスが出力されてしまう
     */
    public function reserveEstimateStatusSortable($query, $direction)
    {
        return $query->select('reserves.*')
            ->leftJoin('v_reserve_custom_values', 'reserves.id', '=', 'v_reserve_custom_values.reserve_id')
            ->where('v_reserve_custom_values.code', config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'))
            ->orWhere('v_reserve_custom_values.code', config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS'))
            ->orderBy('reserves.application_step', $direction)// ⇦予約・見積でソートした後で、ステータスソートした方が良いかと思うので設定。しばらく様子見
            ->orderBy('v_reserve_custom_values.val', $direction);
    }
    

    ///////////////// カスタムソート ここまで /////////////////


    //////////////////// ローカルスコープ ここから /////////////////////////

    /**
     * ASP受付のレコード
     *
     * @param $query
     * @return mixed
     */
    public function scopeAsp($query)
    {
        return $query->where('reception_type', config('consts.reserves.RECEPTION_TYPE_ASP'));
    }

    /**
     * 予約レコード(未催行の予約とキャンセル予約が取得対象)
     * 申込段階が「予約」、且つ帰着日が本日を過ぎていない、もしくはキャンセル
     *
     * @param $query
     * @return mixed
     */
    public function scopeReserve($query)
    {
        return $query
            ->where('application_step', config('consts.reserves.APPLICATION_STEP_RESERVE'))
            ->where(function ($q) {
                $q->where('return_date', '>=', date('Y-m-d'))
                ->orWhereNotNull('cancel_at');
            });
    }

    /**
     * 予約確定前レコード
     *
     * @param $query
     * @return mixed
     */
    public function scopeDraft($query)
    {
        return $query->whereIn(
            'application_step',
            [
                config('consts.reserves.APPLICATION_STEP_CONSULT'), // 相談ステータス(Web受付で使用)
                config('consts.reserves.APPLICATION_STEP_DRAFT'),
            ]
        );
    }

    /**
     * 催行済みレコード
     *
     * この条件を変える場合は 「getIsDepartedAttribute」 も変更のこと
     *
     * 条件:
     * 申込段階(application_step)が「予約」且つ、キャンセルされておらず帰着日が本日を過ぎている
     *
     * @param $query
     * @return mixed
     */
    public function scopeDeparted($query)
    {
        return $query
            ->where('application_step', config('consts.reserves.APPLICATION_STEP_RESERVE'))
            ->whereNull('cancel_at')
            ->where('return_date', '<', date('Y-m-d'));
    }

    //////////////////// ローカルスコープ ここまで /////////////////////////

    ///////////////  集計メソッド ここから ///////////////

    /**
     * 請求金額合計
     */
    public function getSumInvoiceAmountAttribute()
    {
        // reserve_invoicesはsumで集計を取っているが予約との関係は1対1
        return $this->reserve_invoices->sum('amount_total');
    }

    /**
     * 出金額合計
     * 出金詳細レコードのamount計
     */
    public function getSumWithdrawalAttribute()
    {
        return $this->agency_withdrawals->sum('amount');
    }

    /**
     * 未出金額合計
     */
    public function getSumUnpaidAttribute()
    {
        return $this->enabled_account_payable_details->sum('unpaid_balance');
    }

    /**
     * 入金合計
     *
     * 法人顧客のみならagency_bundle_depositsで合計金額が取得できるが、
     * 法人・個人どちらの合計金額も取得したい場合はagency_depositsを使用
     */
    public function getSumDepositAttribute()
    {
        return $this->agency_deposits->sum('amount');
    }

    /**
     * 未入金合計
     */
    public function getSumNotDepositAttribute()
    {
        // reserve_invoicesはsumで集計を取っているが予約との関係は1対1
        return $this->reserve_invoices->sum('amount_total') - $this->agency_deposits->sum('amount');
    }



    //////////////// 以下、HAKKEN専用 /////////////

    /**
     * 目的地表示
     */
    public function getDestinationLabelAttribute($value): string
    {
        if (!$this->dest_direction_id) {
            return '目的地未定';
        } else {
            $str = $this->destination->name ?? '';
            return $str .= $this->destination_place;
        }
    }

    // オンライン相談日程
    public function web_online_schedule()
    {
        return $this->hasOne('App\Models\WebOnlineSchedule');
    }

    //////////////////// ローカルスコープ ここから /////////////////////////

    /**
     * Web受付のレコード
     *
     * @param $query
     * @return mixed
     */
    public function scopeWeb($query)
    {
        return $query->where('reception_type', config('consts.reserves.RECEPTION_TYPE_WEB'));
    }
}
