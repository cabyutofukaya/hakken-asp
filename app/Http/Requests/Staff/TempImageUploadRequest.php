<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class TempImageUploadRequest extends FormRequest
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
            'image' => 'file|max:10000',
        ];
    }
    
    public function messages()
    {
        return [
            'image.file' => 'ファイルをアップロードしてください。',
            'image.max' => '10MBを超える画像はアップロードできません。',
        ];
    }
}
