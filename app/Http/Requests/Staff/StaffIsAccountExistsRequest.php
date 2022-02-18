<?php

namespace App\Http\Requests\Staff;

use App\Services\StaffService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffIsAccountExistsRequest extends FormRequest
{
    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

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
            'account' => ['required','regex:/^[a-zA-Z0-9_\-]+$/',
                function ($attribute, $value, $fail) {
                    if ($this->staffService->isAccountExists(auth('staff')->user()->agency->id, $value)
                    ) {
                        return $fail("そのアカウントはすでに使用されています。");
                    }
                }
            ],
        ];
    }
    
    public function messages()
    {
        return [
            'account.required' => 'アカウントIDは必須です。',
            'account.regex' => 'アカウントIDは半角英数文字(a-z,A-Z,0-9,-_)で入力してください。', 
        ];
    }
}
