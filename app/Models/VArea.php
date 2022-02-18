<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class VArea extends AgencyArea
{
    use Sortable, SoftDeletes;

    protected $primaryKey = 'uuid';

    // 以下、2つのプロパティはuuidをIDにするために必要な設定
    public $incrementing = false;
    protected $keyType = 'string';

    public $sortable = [
        'created_at', 
        'code', 
        'name', 
        'name_en', 
        'v_direction.code',
    ];

    public function v_direction()
    {
        return $this->belongsTo('App\Models\VDirection', 'v_direction_uuid', 'uuid')->withDefault();
    }
}
