<?php

namespace App\Models;

use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class VDirection extends AgencyDirection
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
    ];
}
