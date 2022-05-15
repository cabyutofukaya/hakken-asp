<?php

namespace App\Rules;

use App\Models\Reserve;
use App\Models\ReserveItinerary;
use Illuminate\Contracts\Validation\Rule;

/**
 * 行程が作成されているが有効にチェックが入っていない場合はエラーを出す
 */
class ExistValidItinerary implements Rule
{
    protected $reception;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($reception)
    {
        $this->reception = $reception;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->reception == config('consts.const.RECEPTION_TYPE_ASP')) {
            $reserveId = Reserve::where('estimate_number', $value)->where('reception_type', config('consts.reserves.RECEPTION_TYPE_ASP'))->value("id");
        } elseif ($this->reception == config('consts.const.RECEPTION_TYPE_WEB')) {
            $reserveId = Reserve::where('estimate_number', $value)->where('reception_type', config('consts.reserves.RECEPTION_TYPE_WEB'))->value("id");
        } else {
            return true;
        }
        $rows = ReserveItinerary::where('reserve_id', $reserveId)->get();
        return !($rows->isNotEmpty() && $rows->where('enabled', true)->count() === 0);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '有効行程が設定されていません。';
    }
}
