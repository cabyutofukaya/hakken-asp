<?php

namespace App\Http\Requests\Staff;

use App\Rules\CheckCustomItemNum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCustomItemStoreListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_custom_category_id' => ['required', 'exists:user_custom_categories,id',new CheckCustomItemNum(config("consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST"))],
            'display_position' => 'nullable',
            'name' => 'required',
            'list' => 'nullable|array',
        ];
    }
    
    public function messages()
    {
        return [
        'user_custom_category_id.required' => 'カテゴリは必須です。',
        'user_custom_category_id.exists' => 'カテゴリの指定が不正です。',
        'name.required' => 'リスト項目名は必須です。',
    ];
    }
}
