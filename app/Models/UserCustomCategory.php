<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCustomCategory extends Model
{
    public $timestamps = false;
    
    /**
     * カスタムカテゴリ項目
     */
    public function user_custom_category_items()
    {
        return $this->hasMany('App\Models\UserCustomCategoryItem')->orderBy('seq', 'asc');
    }

    /**
     * カスタム項目
     */
    public function user_custom_items()
    {
        return $this->hasMany('App\Models\UserCustomItem');
    }
}
