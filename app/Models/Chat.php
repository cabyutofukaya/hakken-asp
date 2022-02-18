<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id', 'suggestion_id', 'user_id', 'staff_id', 'message', 'read_at', 'created_at'
    ];

    public function suggestion()
    {
        return $this->belongsTo('App\Models\Suggestion');
    }
}
