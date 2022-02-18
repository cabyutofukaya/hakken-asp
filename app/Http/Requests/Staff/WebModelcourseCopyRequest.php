<?php

namespace App\Http\Requests\Staff;

use App\Rules\ExistStaff;
use Illuminate\Foundation\Http\FormRequest;

class WebModelcourseCopyRequest extends FormRequest
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
            'author_id' => ['required', new ExistStaff(auth('staff')->user()->agency->id)],
        ];
    }
    
    public function messages()
    {
        return [
            'author_id.required' => '作成者IDは必須です。',
        ];
    }
}
