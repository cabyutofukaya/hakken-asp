<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterAreaCsvImportStoreRequest extends FormRequest
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
            'csv.0' => 'required|file|mimes:csv,txt',
            'csv.*' => 'nullable|file|mimes:csv,txt',
        ];
    }
    
    public function messages()
    {
        return [
            'csv.0.required' => 'CSVファイルをアップロードしてください',
            'csv.0.file' => 'CSVファイルをアップロードしてください',
            'csv.0.mimes' => 'CSVファイルをアップロードしてください',
            'csv.*.file' => 'CSVファイルをアップロードしてください',
            'csv.*.mimes' => 'CSVファイルをアップロードしてください',
        ];
    }
}
