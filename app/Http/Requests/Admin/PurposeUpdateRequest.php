<?php

namespace App\Http\Requests\Admin;

use Auth;
use App\Services\Hakken\PurposeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurposeUpdateRequest extends FormRequest
{
    public function __construct(PurposeService $purposeService)
    {
        $this->purposeService = $purposeService;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $purpose = $this->purposeService->find((int)$this->purpose);
        return Auth::guard('admin')->user()->can('update', $purpose); // 権限チェック
    }

    public function validationData()
    {
        return array_merge($this->request->all(), [
            'id' => $this->purpose,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('purpose');
        return [
            'id' => 'required',
            'name' => 'required|string',
            'seq' => 'integer',
        ];
    }
    
    public function messages()
    {
        return [
        'id.required' => 'IDは必須です',
        'name.required' => '名称は必須です',
        'seq.integer' => '順番の指定が不正です',
    ];
    }
}
