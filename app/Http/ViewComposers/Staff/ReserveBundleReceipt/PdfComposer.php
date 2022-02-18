<?php
namespace App\Http\ViewComposers\Staff\ReserveBundleReceipt;

use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * PDFページで使う選択項目などを提供するViewComposer
 */
class PdfComposer
{
    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserveBundleReceipt = Arr::get($data, 'reserveBundleReceipt');

        //////////////////////////////////

        foreach ([
            'user_receipt_number',
            'issue_date',
            'document_receipt_id',
            'document_common_id',
            'document_address',
            'document_setting',
            'document_common_setting',
            'receipt_amount',
        ] as $f) {
            $value[$f] = $reserveBundleReceipt->{$f};
        }

        $formSelects = [
            'honorifics' => get_const_item('documents', 'honorific'), // 敬称
        ];

        $view->with(compact('value', 'formSelects'));
    }
}
