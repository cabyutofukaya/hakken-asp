<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Kyslik\ColumnSortable\Sortable;
use Lang;

class AgencyConsultation extends Model
{
    use ModelLogTrait,SoftDeletes,Sortable;

    public $sortable = [
        'created_at',
        'control_number',
        'reception_date',
        'title',
        'kind',
        'deadline',
        'manager.name',
        'status',
        'reserve.reserve_estimate_number',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'control_number', 
        'agency_id', 
        'taxonomy',
        'user_id',
        'business_user_id',
        'reserve_id', 
        'title', 
        'manager_id', 
        'reception_date', 
        'kind', 
        'deadline', 
        'status', 
        'contents',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    // 予約
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withDefault();
    }

    // 個人顧客
    public function user()
    {
        return $this->belongsTo('App\Models\User')->withDefault();
    }

    // 法人顧客
    public function business_user()
    {
        return $this->belongsTo('App\Models\BusinessUser')->withDefault();
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
    
    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////

    // 受付日
    public function getReceptionDateAttribute($value) : ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 期限
    public function getDeadlineAttribute($value) : ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }
    
    /**
     * カスタム項目を全取得（有効な項目のみ flg=1）
     */
    public function v_agency_consultation_custom_values()
    {
        return $this->hasMany('App\Models\VAgencyConsultationCustomValue')->where('flg', true);
    }

    /**
     * ステータス値を文字に変換
     */
    public function getStatusLabelAttribute(): ?string
    {
        $values = Lang::get('values.agency_consultations.status');
        foreach (config("consts.agency_consultations.STATUS_LIST") as $key => $val) {
            if ($val == $this->status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /**
     * 種別値を文字に変換
     */
    public function getKindLabelAttribute(): ?string
    {
        $values = Lang::get('values.agency_consultations.kind');
        foreach (config("consts.agency_consultations.KIND_LIST") as $key => $val) {
            if ($val == $this->kind) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    ///////////////// 読みやすい文字列に変換するAttribute ここまで //////////////

    //////////////////// ローカルスコープ ここから /////////////////////////

    /**
     * 見積・予約相談
     *
     * @param $query
     * @return mixed
     */
    public function scopeReserve($query)
    {
        return $query->where('taxonomy', config('consts.agency_consultations.TAXONOMY_RESERVE'));
    }

    /**
     * 個人顧客相談
     *
     * @param $query
     * @return mixed
     */
    public function scopePerson($query)
    {
        return $query->where('taxonomy', config('consts.agency_consultations.TAXONOMY_PERSON'));
    }

    /**
     * 法人顧客相談
     *
     * @param $query
     * @return mixed
     */
    public function scopeBusiness($query)
    {
        return $query->where('taxonomy', config('consts.agency_consultations.TAXONOMY_BUSINESS'));
    }

    //////////////////// ローカルスコープ ここまで /////////////////////////
}
