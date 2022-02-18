<?php

namespace App\Rules;

use App\Models\Reserve;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

/**
 * オンライン相談の日付をチェック
 */
class CheckOnlineConsultDate implements Rule
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
        // Zoom APIの仕様では、start_urlの有効期限が発行後90日以内なので、
        // 若干ゆとりを持って制限をかけておく。
        // 参考URL: https://marketplace.zoom.us/docs/api-reference/zoom-api/methods/#tag/Meetings

        $dt = new Carbon($value);

        $after = new Carbon();
        $after->addDays(60); // ひとまず60日以内

        return $dt->lt($after);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '相談日は60日以内で指定してください。';
    }
}
