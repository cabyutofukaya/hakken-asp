<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\EstimateService;
use App\Rules\ExistValidItinerary;

class EstimateDetermineRequest extends FormRequest
{
    public function __construct(EstimateService $estimateService)
    {
        $this->estimateService = $estimateService;
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

    public function validationData()
    {
        return array_merge($this->request->all(), [
            'reception' => $this->reception,
            'estimate_number' => $this->estimateNumber,
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
            'reception' => 'required',
            'estimate_number' => ['required',new ExistValidItinerary($this->reception)],
            'departure_date' => 'required',
            'return_date' => 'required',
            'updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'reception.required' => '受付種別は必須です。',
            'estimate_number.required' => '見積番号は必須です。',
            'departure_date.required' => '出発日が設定されていません。',
            'return_date.required' => '帰着日が設定されていません。',
        ];
    }
}
