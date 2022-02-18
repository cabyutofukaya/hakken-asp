<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

/**
 * Web予約データ
 *
 * reservesテーブルと関連付けし、
 * Web予約に特化したカラムを拡張するテーブル
 */
class WebReserveExt extends Model
{
    use SoftDeletes,ModelLogTrait,SoftCascadeTrait;
    
    // 本テーブルが更新されたらreservesテーブルもupdate_atを更新
    protected $touches = ['reserve'];

    protected $softCascade = [
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'reserve_id',
        'web_consult_id',
        'manager_id', // 担当者はreservesテーブルの同カラムを参照するので、本カラムは基本的にどのマイスターを選択したかを記録しておくためのカラム
        'consent_at',
        'rejection_at',
        'agency_unread_count',
        'user_unread_count',
        'estimate_status',
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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'agency_unread_count' => 'integer'
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

    // 予約情報
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withDefault();
    }

    /**
     * マイスターID
     * 論理削除も取得
     */
    public function manager()
    {
        return $this->belongsTo('App\Models\Staff', 'manager_id')
            ->withTrashed()
            ->withDefault();
    }

    /**
     * 相談データ
     * 論理削除は不要？
     */
    public function web_consult()
    {
        return $this->belongsTo('App\Models\WebConsult')->withDefault();
    }

    /**
     * オンライン相談日程
     */
    public function web_online_schedule()
    {
        return $this->hasOne('App\Models\WebOnlineSchedule')->withDefault();
    }

    // 辞退日時（日付は「YYYY/MM/DD」形式に変換）
    public function getRejectionAtAttribute($value): ?string
    {
        return $value ? date('Y/m/d H:i', strtotime($value)) : null;
    }

    /**
     * 見積ステータス値を文字に変換
     */
    public function getEstimateStatusLabelAttribute(): ?string
    {
        $values = \Lang::get('values.web_reserve_exts.estimate_status');
        foreach (config("consts.web_reserve_exts.ESTIMATE_STATUS_LIST") as $key => $val) {
            if ($val==$this->estimate_status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }
}
