<?php

namespace App\Http\Requests\Admin;

use Auth;
use App\Services\Hakken\PurposeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurposeDestroyRequest extends FormRequest
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
        $purpose = $this->purposeService->find((int)$this->route('purpose'));
        return $purpose && Auth::guard('admin')->user()->can('delete', $purpose); // 権限チェック
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
