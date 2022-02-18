<?php
namespace App\Http\ViewComposers\Staff\ReserveReceipt;

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
        $reserveReceipt = Arr::get($data, 'reserveReceipt');

        //////////////////////////////////

        // $my = auth("staff")->user();
        // $agencyId = $my->agency_id;
        // $agencyAccount = $my->agency->account;

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
            $value[$f] = $reserveReceipt->{$f};
        }

        $formSelects = [
            'honorifics' => get_const_item('documents', 'honorific'), // 敬称
        ];

        $view->with(compact('value', 'formSelects'));
    }
}
