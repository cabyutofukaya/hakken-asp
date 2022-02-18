<?php

namespace App\Http\Requests\Admin;

use Auth;
use App\Services\InterestService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InterestDestroyRequest extends FormRequest
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
        $interest = $this->interestService->find((int)$this->route('interest'));
        return $interest && Auth::guard('admin')->user()->can('delete', $interest); // 権限チェック
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
        return [
            'id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'IDは必須です',
        ];
    }
}
