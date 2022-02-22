<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * パスワードポリシーをチェックするバリデーション
 * 
 * ・半角英数字
 * ・少なくとも1つの特殊文字（!#$%&=-など）が含まれること
 * ・長さ6〜12文字
 */
class CheckPassword implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $escaped = preg_quote('!#$%()*+-./:;=?@[]^_`{|}', '/');
        
        return preg_match("/[a-zA-Z0-9]+/", $value) && preg_match("/[{$escaped}]+/", $value) && strlen($value) >= 6 && strlen($value) <= 12;
    }


    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'パスワードは6〜12文字の半角英数字で、少なくとも1つの特殊文字（!#$%&=-など）を含めて設定してください。';
    }
}
