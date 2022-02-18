<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class WebCompanyUpsertRequest extends FormRequest
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
            'explanation' => 'nullable|max:3000',
            'logo_image' => 'nullable',
            'upload_logo_image' => 'nullable',
            'images' => 'nullable|array',
            'upload_images' => 'nullable|array',
        ];
    }
    
    public function messages()
    {
        return [
            'explanation.max' => '説明文が長すぎます(3000文字まで)。',
            'images.array' => 'イメージ画像の送信形式が不正です。',
            'upload_images.array' => 'イメージ画像(アップロード)の送信形式が不正です。',
        ];
    }
}
