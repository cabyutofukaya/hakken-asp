<?php
namespace App\Http\ViewComposers\Staff\ReserveConfirm;

use App\Services\DocumentCommonService;
use App\Services\DocumentQuoteService;
use App\Services\ReserveService;
use App\Traits\BusinessFormTrait;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 作成・編集ページで使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use JsConstsTrait, BusinessFormTrait;

    public function __construct(
        DocumentCommonService $documentCommonService,
        DocumentQuoteService $documentQuoteService,
        ReserveService $reserveService
    ) {
        $this->documentCommonService = $documentCommonService;
        $this->documentQuoteService = $documentQuoteService;
        $this->reserveService = $reserveService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserveItinerary = Arr::get($data, 'reserveItinerary');
        $applicationStep = Arr::get($data, 'applicationStep');

        $reserveConfirm = Arr::get($data, 'reserveConfirm', null); // 新規作成の場合はnull

        //////////////////////////////////

        $my = auth("staff")->user();
        $agencyId = $my->agency_id;
        $agencyAccount = $my->agency->account;

        // 受付種別
        $reception = config('consts.const.RECEPTION_TYPE_ASP');

        if ($reserveConfirm) { // 保存データあり。$reserveConfirmから各種データをセット

            // 参加者情報
            $participantInfo = $this->reserveService->getParticipants($reserveConfirm->reserve_itinerary->reserve->id, true); // 参加者一覧（取消者を含む。削除済は取得しない）

            // 参加者情報
            $participants = []; // 参加者リスト
            foreach ($participantInfo as $participant) {
                // 取り消し情報もセットする
                $participants[] = $participant->only(['id','name','user_number','sex','name_roman','cancel']);
            }

            $documentQuoteId = $reserveConfirm->document_setting['id'] ?? '';
            $documentCommonId = $reserveConfirm->document_common_id ?? '';

            // 共通設定
            $documentCommonSetting = $reserveConfirm->document_common_setting;

            $documentSetting = $reserveConfirm->document_setting;

            $updatedAt = $reserveConfirm->updated_at->format('Y-m-d H:i:s'); // 同時編集の判定に使用

            /////////// 入力初期値をセット ///////////
            // 帳票番号
            $confirmNumber = $reserveConfirm->confirm_number;
            ;
            // 管理番号
            $controlNumber = $reserveConfirm->control_number;
            // 発行日
            $issueDate = $reserveConfirm->issue_date ?? date('Y/m/d');
            // 案件名
            $name = $reserveConfirm->name;
            // 出発日
            $departureDate = $reserveConfirm->departure_date;
            // 帰着日
            $returnDate = $reserveConfirm->return_date;
            // 担当
            $manager = $reserveConfirm->manager;
            // 代表者情報
            $representative = $reserveConfirm->representative;

            $participantIds = $reserveConfirm->participant_ids; // チェックONの参加者ID
            // 申込者情報
            $documentAddress = $reserveConfirm->document_address ?? $this->getDocumentAddress($reserveConfirm->reserve_itinerary->reserve->applicantable); // 申込者情報が設定されていない場合は予約情報から取得（書類作成後、申込者を変更した場合、本カラムはリセットされている）。

            $status = $reserveConfirm->status;

            
            ///////////////// テンプレートセレクトメニュー /////////////////

            // 編集時、デフォルト系テンプレートが選択されている場合はselectメニュー固定。それ以外はデフォルト系テンプレート以外選択可
            if (in_array($reserveConfirm->document_quote->code, config('consts.reserve_confirms.NO_ADD_OR_DELETE_CODE_LIST'), true)) {
                $documentQuoteSelect = [$reserveConfirm->document_quote_id => $reserveConfirm->document_quote->name];
            } else {
                $documentQuoteSelect = ['' => '---'] + $this->documentQuoteService->getIdNameSelectAppendableSafeValues($agencyId, [$reserveConfirm->document_quote_id]);
            }
        } else { // 新規作成

            // 参加者情報
            $participantInfo = $this->reserveService->getParticipants($reserveItinerary->reserve_id, true); // 参加者一覧（取消者を含む。削除済は取得しない）

            // 参加者情報
            $participants = []; // 参加者リスト
            foreach ($participantInfo as $participant) {
                // 取り消し情報もセットする
                $participants[] = $participant->only(['id','name','user_number','sex','name_roman','cancel']);
            }

            // checkオンの参加者IDリスト（作成時は取消者以外を全てONに）
            $participantIds = $this->getDefaultParticipantCheckIds($participantInfo);

            // 初期値設定用に、デフォルト系以外のテンプレートで並びが一番最初のテンプレートを取得。
            $documentQuote = $this->documentQuoteService->getFirstAppendable($agencyId,false); 

            // 予約確認書のデフォルト設定情報、管理番号
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                // 管理番号
                $controlNumber = $reserveItinerary->reserve->estimate_number;
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                // 管理番号
                $controlNumber = $reserveItinerary->reserve->control_number;
            } else {
                abort(404);
            }

            $documentQuoteId = $documentQuote->id ?? "";
            $documentCommonId = $documentQuote->document_common_id ?? "";
            
            // 共通設定
            $documentCommonSetting = $documentQuote ? $documentQuote->document_common->toArray() : [];

            // 書類設定。$docuemntQuoteが未設定なればsetting配列、sealプロパティ初期化
            $documentSetting = $this->getDocumentSettingSealOrInitSetting($documentQuote->toArray());

            // 「検印欄」の表示・非表示は設定がイレギュラーにつき、他の設定項目と形式を合わせる
            $this->setSealSetting($documentSetting, config('consts.document_quotes.DISPLAY_BLOCK'));

            $updatedAt = null;

            /////////// 入力初期値をセット ///////////
            // 帳票番号
            $confirmNumber = null;
            // 発行日
            $issueDate = date('Y/m/d');
            // 案件名
            $name = $reserveItinerary->reserve->name;
            // 出発日
            $departureDate = $reserveItinerary->reserve->departure_date;
            // 帰着日
            $returnDate = $reserveItinerary->reserve->return_date;
            // 担当
            $manager = $reserveItinerary->reserve->manager->name;
            // 代表者情報
            $representative = $this->getRepresentativeInfo($reserveItinerary->reserve);
            // 申込者情報
            $documentAddress = $this->getDocumentAddress($reserveItinerary->reserve->applicantable);

            $status = config('consts.reserve_confirms.STATUS_DEFAULT');


            ///////////////// テンプレートセレクトメニュー /////////////////

            // 新規作成時はユーザー追加テンプレートのみ追加可能（デフォルト系テンプレートは追加不可）
            $documentQuoteSelect = ['' => '---'] + $this->documentQuoteService->getIdNameSelectAppendable($agencyId, false);
        }

        // 各種デフォルト
        $defaultValue = [
            'document_quote_id' => $documentQuoteId,
            'document_common_id' => $documentCommonId,
            // 帳票番号
            'confirm_number' => $confirmNumber,
            // 管理番号
            'control_number' => $controlNumber,
            // 発行日
            'issue_date' => $issueDate,
            // 案件名
            'name' => $name,
            // 出発日
            'departure_date' => $departureDate,
            // 帰着日
            'return_date' => $returnDate,
            // 担当
            'manager' => $manager,
            // 代表者情報
            'representative' => $representative,
            'participant_ids' => $participantIds, // チェックONの参加者ID
            'document_address' => $documentAddress,
            'updated_at' => $updatedAt, // 同時編集制御
            'status' => $status,
        ];

        $formSelects = [
            'documentCommons' => ['' => '---'] + $this->documentCommonService->getIdNameSelectSafeValues($agencyId, [$documentCommonId]), // 宛名/自社情報共通設定selectメニュー
            'documentQuotes' => $documentQuoteSelect,
            'participants' => $participants,
            'honorifics' => get_const_item('documents', 'honorific'), // 敬称
            'statuses' => get_const_item('reserve_confirms', 'status'), // ステータス
            'setting' => [
                config('consts.document_quotes.DISPLAY_BLOCK') => $this->structuralChange(config('consts.document_quotes.DISPLAY_BLOCK_LIST')),
                config('consts.document_quotes.RESERVATION_INFO') => $this->structuralChange(config('consts.document_quotes.RESERVATION_INFO_LIST')),
                config('consts.document_quotes.AIR_TICKET_INFO') => $this->structuralChange(config('consts.document_quotes.AIR_TICKET_INFO_LIST')),
                config('consts.document_quotes.BREAKDOWN_PRICE') => $this->structuralChange(config('consts.document_quotes.BREAKDOWN_PRICE_LIST'))
            ],
        ];

        $consts = $this->getConstDatas();

        // オプション価格情報、航空券価格情報、ホテル価格情報、宿泊施設情報、宿泊施設連絡先を取得
        list($optionPrices, $airticketPrices, $hotelPrices, $hotelInfo, $hotelContacts) = $this->getPriceAndHotelInfo($reserveItinerary);

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('defaultValue', 'formSelects', 'consts', 'documentCommonSetting', 'hotelContacts', 'hotelInfo', 'optionPrices', 'airticketPrices', 'hotelPrices', 'documentSetting', 'jsVars', 'reception'));
    }
}
