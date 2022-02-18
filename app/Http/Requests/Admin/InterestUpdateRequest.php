<?php

namespace App\Http\Requests\Admin;

use Auth;
use App\Services\InterestService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InterestUpdateRequest extends FormRequest
{
    public function __construct(InterestService $interestService)
    {
        $this->interestService = $interestService;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $interest = $this->interestService->find((int)$this->interest);
        return Auth::guard('admin')->user()->can('update', $interest); // 権限チェック
    }

    public function validationData()
    {
        return array_merge($this->request->all(), [
            'id' => $this->interest,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('interest');
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
