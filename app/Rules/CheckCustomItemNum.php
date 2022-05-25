<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * カスタム項目数の上限チェック
 *
 */
class CheckCustomItemNum implements Rule
{
    protected $type;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $type)
    {
        $this->type = $type;
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
        $userCustomCategoryItem = \App\Models\UserCustomCategoryItem::where('user_custom_category_id', $value)->where('type', $this->type)->first();

        return $userCustomCategoryItem->user_custom_items_for_agency->count() < config('consts.const.CUSTOM_ITEM_MAX_NUM');
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'カスタム項目の作成上限を超えています(' .  config('consts.const.CUSTOM_ITEM_MAX_NUM') . '個まで)。';
    }
}
