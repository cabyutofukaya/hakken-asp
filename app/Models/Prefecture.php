<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Prefecture extends Model
{
    use Sortable;

    protected $fillable = [ 'code','name','block_name' ];
    public $sortable = ['id','code'];

}
