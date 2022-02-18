<?php

namespace App\Rules;

use App\Models\DocumentQuote;
use Illuminate\Contracts\Validation\Rule;

/**
 * POSTされたIDがdocument_quotesレコードに存在するかチェックするバリデーション
 */
class ExistDocumentQuote implements Rule
{
    protected $agencyId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(int $agencyId)
    {
        $this->agencyId = $agencyId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // IDが存在するかチェック(論理削除も含めて検索)
        return DocumentQuote::withTrashed()->where('agency_id', $this->agencyId)->where('id', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attributeの指定が不正です（テンプレート）。';
    }
}
