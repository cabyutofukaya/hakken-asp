<?php

namespace App\Http\Requests\Staff;

use App\Services\AccountPayableItemService;
use App\Services\AccountPayableDetailService;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExistStaff;

class AgencyWithdrawalItemHistoryStoreRequest extends FormRequest
{
    public function __construct(AccountPayableItemService $accountPayableItemService, AccountPayableDetailService $accountPayableDetailService)
    {
        $this->accountPayableItemService = $accountPayableItemService;
        $this->accountPayableDetailService = $accountPayableDetailService;
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
            'account_payable_item_id' => $this->accountPayableItemId,
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
            'account_payable_item_id' => 'required',
            'amount' => ['required','integer',function ($attribute, $value, $fail) {
                $accountPayableItem = $this->accountPayableItemService->find($this->accountPayableItemId);

                try {
                    if ($accountPayableItem->unpaid_balance == 0) { // ゼロ除算禁止
                        throw new \Exception("未払額はありません。");
                    }
    
                    // 出金割合(1, 0.5 ... etc)
                    $rate = get_agency_withdrawal_rate($value, $accountPayableItem->unpaid_balance);
                    
                    if (!preg_match('/^[0-9\-]+$/', $rate * 100)) { // パーセンテージが割り切れないケース。数字とマイナス記号のみの構成であること(マイナス記号は必要か不明だが、一応許可しておく)
                        throw new \Exception("未払金額に対する出金額の割合が正しくありません。");
                    }

                    $total = 0;
                    $this->accountPayableDetailService->getSummarizeItemQuery(
                            $accountPayableItem->toArray()
                    )->chunk(100, function ($rows) use ($rate, &$total) { // 念の為100件ずつ取得
                        foreach ($rows as $row) {
                            $p = $row->unpaid_balance * $rate;
                            if (!preg_match('/^[0-9\-]+$/', $p)) { // 商品仕入額のパーセンテージが割り切れないケース。数字とマイナス記号のみの構成であること(マイナス記号は必要か不明だが、一応許可しておく)
                                throw new \Exception("未払金額に対する出金額の割合が正しくありません。");
                            }
                            $total += $p;
                        }
                    });

                    if ($total != $value) { // 一応、計算が合うかチェック
                        throw new \Exception("出金額の入力が正しくありません。");
                    }
                } catch (\Exception $e) {
                    $fail($e->getMessage());
                    return;
                }
            }],
            'withdrawal_date' => 'nullable|date',
            'record_date' => 'nullable|date',
            'manager_id' => ['nullable', new ExistStaff(auth('staff')->user()->agency->id)],
            'note' => 'nullable|max:1500',
            'account_payable_item.updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'account_payable_item_id.required' => '仕入詳細IDは必須です。',
            'amount.required' => '出金額は必須です。',
            'amount.integer' => '出金額は半角数字で入力してください',
            'amount.integer' => '出金額は半角数字で入力してください',
            'withdrawal_date.date' => '出金日の入力入力形式が不正です(YYYY/MM/DD)',
            'record_date.date' => '登録日の入力入力形式が不正です(YYYY/MM/DD)',
            'participant_id.required' => '参加者IDは必須です。',
            'note.max' => '備考が長すぎます(1500文字まで)',
        ];
    }
}
