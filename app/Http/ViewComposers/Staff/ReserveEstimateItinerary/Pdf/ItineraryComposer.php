<?php
namespace App\Http\ViewComposers\Staff\ReserveEstimateItinerary\Pdf;

use App\Services\DocumentCommonService;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * pdfで使う選択項目などを提供するViewComposer
 */
class ItineraryComposer
{
    public function __construct(DocumentCommonService $documentCommonService)
    {
        $this->documentCommonService = $documentCommonService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserveItinerary = Arr::get($data, 'reserveItinerary');

        //////////////////////////////////

        // 会社名・TEL・FAX表記のためデフォルト会社情報を取得
        $companyInfo = $this->documentCommonService->getDefault(
            auth('staff')->user()->agency_id, 
            ['company_name', 'tel', 'fax']
        );
        
        $consts = [
            'thumbMBaseUrl' => \Storage::disk('s3')->url(config('consts.const.UPLOAD_THUMB_M_DIR')),
            'companyInfo' => $companyInfo,
        ];

        $view->with(compact('consts'));
    }
}
