<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

// 未使用
/**
 * 商品コードの使用文字列が適正かチェックするバリデーション
 */
class CheckCodeFormat implements Rule
{
    // /**
    //  * Determine if the validation rule passes.
    //  *
    //  * @param  string  $attribute
    //  * @param  mixed  $value
    //  * @return bool
    //  */
    // public function passes($attribute, $value)
    // {
    //     return preg_match("/^[a-zA-Z0-9\-_\.\!'\(\)]+$/", $value);
    // }

    // /**
    //  * Get the validation error message.
    //  *
    //  * @return string
    //  */
    // public function message()
    // {
    //     return '商品コードは半角英数、「-(ハイフン)」「_(アンダーバー)」が利用できます。';
    // }
}
