<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ExistSupplier;

class ReserveItineraryStoreRequest extends FormRequest
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
            'note' => 'nullable|max:3000',
            'dates.*.*.type' => 'required',
            // 'dates.*.*.seq' => 'nullable|numeric',
            'dates.*.*.arrival_time' => 'nullable|max:16',
            'dates.*.*.staying_time' => 'nullable|max:16',
            'dates.*.*.departure_time' => 'nullable|max:16',
            'dates.*.*.place' => 'nullable|max:100',
            'dates.*.*.explanation' => 'nullable|max:300',
            'dates.*.*.transportation' => ['nullable',Rule::in(array_values(config("consts.reserve_itineraries.TRANSPORTATION_LIST")))],
            'dates.*.*.transportation_supplement' => 'nullable|max:100',
            // 'dates.*.*.travel_date' => 'required|date',
            // reserve_purchasing_subjects
            'dates.*.*.reserve_purchasing_subjects' => 'nullable|array',
            'dates.*.*.reserve_purchasing_subjects.*.subject' => ['required',Rule::in(array_values(config("consts.subject_categories.SUBJECT_CATEGORY_LIST")))],
            'dates.*.*.reserve_purchasing_subjects.*.supplier_id' => ['required',new ExistSupplier(auth('staff')->user()->agency->id)],
            'dates.*.*.reserve_purchasing_subjects.*.ad_zei_kbn' => ['nullable',Rule::in(array_values(config("consts.subject_categories.ZEI_KBN_LIST")))],
            'dates.*.*.reserve_purchasing_subjects.*.ch_zei_kbn' => ['nullable',Rule::in(array_values(config("consts.subject_categories.ZEI_KBN_LIST")))],
            'dates.*.*.reserve_purchasing_subjects.*.inf_zei_kbn' => ['nullable',Rule::in(array_values(config("consts.subject_categories.ZEI_KBN_LIST")))],
            //　任意項目
            'dates.*.*.reserve_purchasing_subjects.*.name' => 'nullable|max:100',
            'dates.*.*.reserve_purchasing_subjects.*.address' => 'nullable|max:100',
            'dates.*.*.reserve_purchasing_subjects.*.tel' => 'nullable|max:100',
            'dates.*.*.reserve_purchasing_subjects.*.fax' => 'nullable|max:100',
            'dates.*.*.reserve_purchasing_subjects.*.url' => 'nullable|max:100',
            'dates.*.*.reserve_purchasing_subjects.*.booking_class' => 'nullable|max:100',
            // 料金
            'dates.*.*.reserve_purchasing_subjects.*.ad_gross_ex' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.ad_gross' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.ad_gross_profit' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.ad_cost' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.ad_net' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.ad_commission_rate' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.ch_gross_ex' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.ch_gross' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.ch_gross_profit' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.ch_cost' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.ch_net' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.ch_commission_rate' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.inf_gross_ex' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.inf_gross' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.inf_gross_profit' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.inf_cost' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.inf_net' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.inf_commission_rate' => 'nullable|integer',
            // participants
            'dates.*.*.reserve_purchasing_subjects.*.participants' => 'nullable|array',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.participant_id' => 'required',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.zei_kbn' => ['nullable',Rule::in(array_values(config("consts.subject_categories.ZEI_KBN_LIST")))],
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.age_kbn' => ['nullable',Rule::in(array_values(config("consts.users.AGE_KBN_LIST")))],
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.valid' => 'required|boolean',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.purchase_type' => 'required',
            // 料金
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.gross_ex' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.gross' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.gross_profit' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.cost' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.net' => 'nullable|integer',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.commission_rate' => 'nullable|integer',
        ];
    }
    
    public function messages()
    {
        return [
            'note.max' => '備考が長過ぎます。',
            'dates.*.*.type.required' => '日程種別は必須です。',
            // 'dates.*.*.seq.numeric' => '並び順の値が数字以外で指定されています。',
            'dates.*.*.arrival_time.max' => '到着時間が長すぎます(16文字まで)。',
            'dates.*.*.staying_time.max' => '滞在時間が長すぎます(16文字まで)。',
            'dates.*.*.departure_time.max' => '出発時間が長すぎます(16文字まで)。',
            'dates.*.*.place.max' => '場所が長すぎます(100文字まで)。',
            'dates.*.*.explanation.max' => '説明が長すぎます(300文字まで)。',
            'dates.*.*.transportation.in' => '移動手段の指定が不正です。',
            'dates.*.*.transportation_supplement.max' => '出発時間が長すぎます(100文字まで)。',
            // 'dates.*.*.travel_date.required' => '旅行日は必須です。',
            // 'dates.*.*.travel_date.date' => '旅行日の形式が不正です。',
            // reserve_purchasing_subjects
            'dates.*.*.reserve_purchasing_subjects.array' => '仕入科目のデータ形式が不正です。',
            'dates.*.*.reserve_purchasing_subjects.*.subject.required' => '科目は必須です。',
            'dates.*.*.reserve_purchasing_subjects.*.supplier_id.required' => '仕入先は必須です。',
            'dates.*.*.reserve_purchasing_subjects.*.subject.in' => '科目の指定が不正です。',
            'dates.*.*.reserve_purchasing_subjects.*.ad_zei_kbn.in' => '税区分の指定が不正です(ad_zei_kbn)。',
            'dates.*.*.reserve_purchasing_subjects.*.ch_zei_kbn.in' => '税区分の指定が不正です(ch_zei_kbn)。',
            'dates.*.*.reserve_purchasing_subjects.*.inf_zei_kbn.in' => '税区分の指定が不正です(inf_zei_kbn)。',
            // 任意項目
            'dates.*.*.reserve_purchasing_subjects.*.name.max' => '名称が長すぎます。',
            'dates.*.*.reserve_purchasing_subjects.*.address.max' => '住所が長すぎます。',
            'dates.*.*.reserve_purchasing_subjects.*.tel.max' => '電話番号が長すぎます。',
            'dates.*.*.reserve_purchasing_subjects.*.fax.max' => 'FAX番号が長すぎます。',
            'dates.*.*.reserve_purchasing_subjects.*.url.max' => 'URLが長すぎます。',
            'dates.*.*.reserve_purchasing_subjects.*.booking_class.max' => '予約クラスが長すぎます。',
            // 料金
            'dates.*.*.reserve_purchasing_subjects.*.ad_gross_ex.integer' => '税抜GROSS単価(大人)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.ad_gross.integer' => 'GROSS単価(大人)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.ad_gross_profit.integer' => '粗利(大人)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.ad_cost.integer' => '仕入(大人)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.ad_net.integer' => 'NET(大人)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.ad_commission_rate.integer' => '手数料率(大人)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.ch_gross_ex.integer' => '税抜GROSS単価(子供)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.ch_gross.integer' => 'GROSS単価(子供)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.ch_gross_profit.integer' => '粗利(子供)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.ch_cost.integer' => '仕入(子供)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.ch_net.integer' => 'NET(子供)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.ch_commission_rate.integer' => '手数料率(子供)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.inf_gross_ex.integer' => '税抜GROSS単価(幼児)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.inf_gross.integer' => 'GROSS単価(幼児)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.inf_gross_profit.integer' => '粗利(幼児)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.inf_cost.integer' => '仕入(幼児)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.inf_net.integer' => 'NET(幼児)は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.inf_commission_rate.integer' => '手数料率(幼児)は半角数字で入力してください。',
            // participants
            'dates.*.*.reserve_purchasing_subjects.*.participants.array' => '参加者料金テーブルのデータ形式が不正です。',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.participant_id.required' => '参加者IDが設定されていません。',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.zei_kbn.in' => '税区分の指定が不正です(zei_kbn)。',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.age_kbn.in' => '年齢区分の指定が不正です。',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.valid.boolean' => '有効フラグの値が不正です。',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.purchase_type.required' => '仕入種別は必須です。',
            // 料金
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.gross_ex.integer' => '税抜GROSS単価は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.gross.integer' => 'GROSS単価は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.gross_profit.integer' => '粗利は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.cost.integer' => '仕入は半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.net.integer' => 'NETは半角数字で入力してください。',
            'dates.*.*.reserve_purchasing_subjects.*.participants.*.commission_rate.integer' => '手数料率は半角数字で入力してください。',
        ];
    }
}
