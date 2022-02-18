<?php

namespace App\Models;

use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Model;

class ModelLog extends Model
{
    use Sortable;

    const UPDATED_AT = null;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model',
        'model_id',
        'guard',
        'user_id',
        'operation_type',
        'message',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public $sortable = ['id'];

}
