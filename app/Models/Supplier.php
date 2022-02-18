<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Traits\HashidsTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Supplier extends Model
{
    use Sortable, SoftDeletes, ModelLogTrait, SoftCascadeTrait, HashidsTrait;
    
    public $sortable = ['id', 'code', 'name'];

    protected $softCascade = [
        'supplier_account_payables'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'code',
        'name',
        'reference_date',
        // 'payday',
        'cutoff_date',
        'payment_month',
        'payment_day',
        'account_payable',
        'note',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    /**
     * 振込先
     */
    public function supplier_account_payables()
    {
        return $this->hasMany('App\Models\SupplierAccountPayable');
    }

    /**
     * カスタム項目を全取得（有効な項目のみ flg=1）
     */
    public function v_supplier_custom_values()
    {
        return $this->hasMany('App\Models\VSupplierCustomValue')->where('flg', true);
    }

    /**
     * 仕入先名称
     * 
     * 論理削除のデータを表示する際は末尾に (削除) を明記
     */
    public function getNameAttribute($value): ?string
    {
        return $this->trashed() ? sprintf("%s(削除)", $value) : $value;
    }

}
