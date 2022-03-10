<?php

namespace App\Models;

use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ActLog extends Model
{
    use Sortable;
    
    protected $connection;

    const UPDATED_AT = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $connection = env('DB_LOG_CONNECTION', env('DB_CONNECTION', 'mysql'));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'guard',
        'route',
        'url',
        'method',
        'status',
        'message',
        'remote_addr',
        'user_agent',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $dates = [
        'created_at',
    ];

    // カラム暗号化
    public function setMessageAttribute($value)
    {
        if ($value) {
            $arr = json_decode($value, true);
            if (isset($arr['password'])) {
                $arr['password'] = str_repeat("*", strlen($arr['password']));//パスワード文字列は記録しない
                $value = json_encode($arr);
            }
            // $this->attributes['message'] = Crypt::encrypt(serialize($value));
            $this->attributes['message'] = $value;
        }
    }

    // カラム復号化
    public function getMessageAttribute($value)
    {
        // if ($value) {
        //     return unserialize(Crypt::decrypt($value));
        // }
        return $value;
    }
}
