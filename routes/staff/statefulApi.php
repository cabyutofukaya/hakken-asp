<?php

use Illuminate\Http\Request;

Route::pattern('id', '[0-9]+');
Route::pattern('applicationStep', 'normal|reserve');
Route::pattern('reception', 'web|asp');


Route::domain(env('STAFF_DOMAIN', 'api.hakken-tour.com'))->namespace('Staff\Api')->name('staff.api.')->prefix('api/{agencyAccount}')->group(function () {
    // 要認証API
    Route::middleware('api_auth:staff', 'check.company')->group(function () {

        Route::post('upload/image/temp', 'FileUploadController@tempImageUpload'); // 一時画像アップロード

        Route::get('staff/list', 'StaffController@index');
        Route::get('listAgencyRoles', 'AgencyRoleController@index');
        Route::get('individuals', 'IndividualController@index');
        Route::put('toggleFlg', 'UserCustomItemController@toggleFlg');
        Route::post('is-account-exists', 'StaffController@isAccountExists');
        Route::get('agency-role/list', 'AgencyRoleController@names');

        Route::get('agency-notification/list', 'AgencyNotificationController@index'); // 通知一覧を取得
        Route::put('agency-notification/read', 'AgencyNotificationController@read'); // 通知の既読処理

        Route::get('custom/list/category-code/{code}', 'UserCustomItemController@getByCategoryCode'); // 当該カテゴリコードに紐づくカスタム項目を取得
        Route::put('user/{account}/status', 'StaffController@statusUpdate'); // ステータスを更新
        Route::delete('user/{account}', 'StaffController@destroy'); // アカウント削除

        // 予約
        Route::get('reserve/list', 'ReserveController@index'); // 一覧
        Route::get('reserve/{reserveNumber}', 'ReserveController@show'); // 詳細
        Route::get('participant/search', 'ParticipantController@participantSearch'); // 顧客検索
        Route::delete('reserve/{reserveNumber}', 'ReserveController@destroy'); // 削除
        Route::put('reserve/{reserveNumber}/no-cancel-charge/cancel', 'ReserveController@noCancelChargeCancel'); // キャンセル
        Route::get('v_area/search', 'ReserveController@vAreaSearch'); // 国・地域検索
        Route::put('reserve/{reserveNumber}/status', 'ReserveController@statusUpdate'); // ステータスを更新

        // 見積
        Route::get('estimate/list', 'EstimateController@index'); // 一覧
        Route::get('estimate/{estimateNumber}', 'EstimateController@show'); // 詳細
        Route::delete('estimate/{estimateNumber}', 'EstimateController@destroy'); // 削除
        Route::put('asp/estimate/{estimateNumber}/determine', 'EstimateController@determine')->name('asp.estimate.determine'); // 見積確定
        Route::put('estimate/{estimateNumber}/status', 'EstimateController@statusUpdate'); // ステータスを更新
        
        // 催行済み
        Route::get('departed/list', 'DepartedController@index'); // 一覧
        Route::delete('departed/{hashId}', 'DepartedController@destroy'); // 削除

        // 予約行程
        Route::get('estimate/{reception}/{applicationStep}/{controlNumber}/itinerary/list', 'ReserveItineraryController@index'); // 一覧
        Route::put('estimate/{reception}/{applicationStep}/{controlNumber}/itinerary/enabled', 'ReserveItineraryController@setEnabled'); // 有効チェック
        Route::delete('estimate/{reception}/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}', 'ReserveItineraryController@destroy'); // 削除

        // 予約確認書・見積帳票
        Route::get('estimate/{reception}/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/confirm/list', 'ReserveConfirmController@index'); // 一覧
        Route::post('estimate/{reception}/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/confirm', 'ReserveConfirmController@store'); // 作成
        Route::put('estimate/{reception}/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/confirm/{confirmNumber}', 'ReserveConfirmController@update'); // 更新
        Route::delete('estimate/{reception}/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/confirm/{confirmNumber}', 'ReserveConfirmController@destroy'); // 削除
        Route::put('reserve_confirm/{id}/status', 'ReserveConfirmController@statusUpdate'); // ステータス更新


        // 請求書
        Route::put('estimate/{reception}/reserve/{reserveNumber}/invoice', 'ReserveInvoiceController@upsert'); // 作成or更新
        Route::put('management/deposit_batch', 'ReserveInvoiceController@depositBatch'); // 一括入金処理(一括請求、一括請求内訳ページで使用)
        Route::put('invoice/{id}/status', 'ReserveInvoiceController@statusUpdate'); // ステータス更新


        // 領収書(通常)
        Route::put('estimate/{reception}/reserve/{reserveNumber}/receipt', 'ReserveReceiptController@upsert'); // 作成or更新
        Route::put('receipt/{id}/status', 'ReserveReceiptController@statusUpdate'); // ステータス更新
        // 領収書(一括請求用)
        Route::put('management/bundle_receipt/{reserveBundleInvoiceHashId}', 'ReserveBundleReceiptController@upsert'); // 作成or更新
        Route::put('bundle_receipt/{id}/status', 'ReserveBundleReceiptController@statusUpdate'); // ステータス更新


        // 支払管理
        Route::get('{reception}/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/payable/list', 'AccountPayableController@index'); // 仕入先毎一覧（予約・見積詳細の「仕入れ先買掛金」枠）

        Route::get('management/payment/list', 'AccountPayableDetailController@index'); 
        Route::post('management/withdrawal/account_payable_detail/{accountPayableDetailId}', 'AgencyWithdrawalController@store')->where('accountPayableDetailId', '[0-9]+'); // 出金登録
        Route::delete('management/withdrawal/{agencyWithdrawalId}', 'AgencyWithdrawalController@destroy'); // 出金登録削除
        Route::put('management/account_payable_detail/{accountPayableDetailId}', 'AccountPayableDetailController@update')->where('accountPayableDetailId', '[0-9]+'); // 支払情報編集
        Route::put('management/account_payable_detail/payment_batch', 'AccountPayableDetailController@paymentBatch'); // 支払一括処理

        // 請求管理
        Route::get('management/invoice/list', 'VReserveInvoiceController@index'); // 一覧
        Route::post('management/deposit/reserve_invoice/{reserveInvoiceId}', 'AgencyDepositController@store')->where('reserveInvoiceId', '[0-9]+'); // 入金登録
        Route::delete('management/deposit/{agencyDepositId}', 'AgencyDepositController@destroy'); // 入金登録削除
        Route::post('management/deposit/reserve_bundle_invoice/{reserveBundleInvoiceId}', 'AgencyBundleDepositController@store')->where('reserveBundleInvoiceId', '[0-9]+'); // 一括入金登録
        Route::delete('management/bundle_deposit/{agencyBundleDepositId}', 'AgencyBundleDepositController@destroy'); // 一括入金登録削除
        
        // 一括請求書
        Route::put('management/bundle_invoice/{reserveBundleInvoiceId}', 'ReserveBundleInvoiceController@edit'); // 更新
        Route::get('management/bundle_invoice/{reserveBundleInvoiceId}/breakdown/list', 'ReserveBundleInvoiceController@breakdownOfBundle'); // 一括請求内訳一覧
        Route::put('bundle_invoice/{id}/status', 'ReserveBundleInvoiceController@statusUpdate'); // ステータス更新


        // 科目前半
        Route::post('subject/search', 'SubjectController@search'); // 科目検索


        // 予約・見積相談
        Route::get('estimate/{applicationStep}/{controlNumber}/consultation/list', 'ReserveConsultationController@index'); // 一覧
        Route::post('estimate/{applicationStep}/{reserveNumber}/consultation', 'ReserveConsultationController@store'); // 作成
        Route::put('estimate/{applicationStep}/{reserveNumber}/consultation/{consulNumber}', 'ReserveConsultationController@update'); // 更新

        // 工程ページ
        Route::get('purchasing_subject/{subject}/{id}/exist_withdrawal', 'ReservePurchasingSubjectController@existSubjectWithdrawal'); // 出金登録チェック(仕入科目削除時)
        Route::get('reserve_schedule/{id}/exist_withdrawal', 'ReservePurchasingSubjectController@existScheduleWithdrawal'); // 出金登録チェック(行程削除時)

        // 相談履歴
        Route::get('consultation/list', 'AgencyConsultationController@index'); // 一覧
        Route::put('consultation/{id}', 'AgencyConsultationController@update'); // 更新
        Route::get('consultation/message/list', 'AgencyConsultationController@messageIndex'); // メッセージ履歴一覧

        // ユーザー作成
        Route::post('client/person', 'UserController@store'); // 作成

        // 参加者。URLパラメータに申込段階(見積/予約)を付与
        Route::get('/estimate/{reception}/{applicationStep}/{controlNumber}/participant/list', 'ParticipantController@index'); // 一覧
        Route::post('/estimate/{reception}/{applicationStep}/{controlNumber}/participant', 'ParticipantController@store'); // 作成
        Route::put('/estimate/{reception}/{applicationStep}/{controlNumber}/participant/{id}', 'ParticipantController@update'); // 更新
        Route::put('/estimate/{reception}/{applicationStep}/{controlNumber}/representative', 'ParticipantController@setRepresentative'); // 代表者更新
        Route::put('/estimate/{reception}/{applicationStep}/{controlNumber}/participant/{id}/cancel', 'ParticipantController@setCancel'); // 取消
        Route::delete('/estimate/{reception}/{applicationStep}/{controlNumber}/participant/{id}', 'ParticipantController@destroy'); // 削除

        Route::get('mail/list', 'MailTemplateController@index'); // メールテンプレート一覧
        Route::delete('mail/{hashId}', 'MailTemplateController@destroy'); // メールテンプレート削除

        // 帳票設定
        Route::get('document/{code}/list', 'DocumentController@index'); // 帳票一覧
        Route::delete('document/{code}/{hashId}', 'DocumentController@destroy'); // 帳票設定削除

        // 帳票共通設定
        Route::get('document_common/{id}', 'DocumentCommonController@show'); // 共通設定取得
        Route::get('document_quote/{id}', 'DocumentQuoteController@show'); // 見積・予約確認書設定取得
        Route::get('document_request/{id}', 'DocumentRequestController@show'); // 請求書設定
        Route::get('document_request_all/{id}', 'DocumentRequestAllController@show'); // 一括請求書設定
        Route::get('document_receipt/{id}', 'DocumentReceiptController@show'); // 領収書設定


        // 方面
        // Route::get('direction/list', 'DirectionController@index'); // 一覧
        Route::get('direction/list', 'DirectionController@index'); // 一覧
        Route::delete('direction/{uuid}', 'DirectionController@destroy'); // 削除
        
        Route::get('v_direction/search', 'VDirectionController@search'); // 方面検索

        // 国・地域
        Route::get('area/list', 'AreaController@index'); // 一覧
        Route::delete('area/{uuid}', 'AreaController@destroy'); // 削除

        // 都市・空港
        Route::get('city/list', 'CityController@index'); // 一覧
        Route::delete('city/{city}', 'CityController@destroy'); // 削除
        Route::get('city/search', 'CityController@search'); // 都市・空港検索

        // オプション科目（科目マスタ）
        Route::get('subject/option/list', 'SubjectOptionController@index'); // 一覧
        Route::delete('subject/option/{subjectOption}', 'SubjectOptionController@destroy'); // 削除

        // 航空券科目（科目マスタ）
        Route::get('subject/airplane/list', 'SubjectAirplaneController@index'); // 一覧
        Route::delete('subject/airplane/{subjectAirplane}', 'SubjectAirplaneController@destroy'); // 削除

        // ホテル科目（科目マスタ）
        Route::get('subject/hotel/list', 'SubjectHotelController@index'); // 一覧
        Route::delete('subject/hotel/{subjectHotel}', 'SubjectHotelController@destroy'); // 削除

        // 個人顧客
        Route::get('client/person/list', 'UserController@index'); // 一覧
        Route::put('client/person/{userNumber}/status', 'UserController@statusUpdate'); // ステータス更新
        Route::delete('client/person/{userNumber}', 'UserController@destroy'); // 削除
        Route::get('client/person/{userNumber}/usage_history/list', 'UserController@usageHistory'); // 利用履歴一覧

        // 個人顧客相談
        Route::get('client/person/{userNumber}/consultation/list', 'UserConsultationController@index'); // 一覧
        Route::post('client/person/{userNumber}/consultation', 'UserConsultationController@store'); // 作成
        Route::put('client/person/{userNumber}/consultation/{consultationNumber}', 'UserConsultationController@update'); // 更新

        // 法人顧客
        Route::get('client/business/list', 'BusinessUserController@index'); // 一覧
        Route::put('client/business/{userNumber}/status', 'BusinessUserController@statusUpdate'); // ステータス更新
        Route::delete('client/business/{userNumber}', 'BusinessUserController@destroy'); // 削除
        Route::get('client/business/{userNumber}/usage_history/list', 'BusinessUserController@usageHistory'); // 利用履歴一覧

        // 法人顧客相談
        Route::get('client/business/{userNumber}/consultation/list', 'BusinessUserConsultationController@index'); // 一覧
        Route::post('client/business/{userNumber}/consultation', 'BusinessUserConsultationController@store'); // 作成
        Route::put('client/business/{userNumber}/consultation/{consultationNumber}', 'BusinessUserConsultationController@update'); // 更新


        // 取引先担当者情報
        Route::get('client/business/{userNumber}/manager/list', 'BusinessUserManagerController@index'); // 一覧
        Route::post('client/business/{userNumber}/manager', 'BusinessUserManagerController@store'); // 作成
        Route::put('client/business/{userNumber}/manager/{businessUserManager}', 'BusinessUserManagerController@update'); // 更新
        Route::delete('client/business/{userNumber}/manager/{businessUserManager}', 'BusinessUserManagerController@destroy'); // 削除


        // ビザ情報
        Route::get('client/person/{userNumber}/visa/list', 'UserVisaController@index'); // 一覧
        Route::post('client/person/{userNumber}/visa', 'UserVisaController@store'); // 作成
        Route::put('client/person/{userNumber}/visa/{userVisa}', 'UserVisaController@update'); // 更新
        Route::delete('client/person/{userNumber}/visa/{userVisa}', 'UserVisaController@destroy'); // 削除

        // マイレージ情報
        Route::get('client/person/{userNumber}/mileage/list', 'UserMileageController@index'); // 一覧
        Route::post('client/person/{userNumber}/mileage', 'UserMileageController@store'); // 作成
        Route::put('client/person/{userNumber}/mileage/{userMileage}', 'UserMileageController@update'); // 更新
        Route::delete('client/person/{userNumber}/mileage/{userMileage}', 'UserMileageController@destroy'); // 削除

        // メンバーカード情報
        Route::get('client/person/{userNumber}/card/list', 'UserMemberCardController@index'); // 一覧
        Route::post('client/person/{userNumber}/card', 'UserMemberCardController@store'); // 作成
        Route::put('client/person/{userNumber}/card/{userMemberCard}', 'UserMemberCardController@update'); // 更新
        Route::delete('client/person/{userNumber}/card/{userMemberCard}', 'UserMemberCardController@destroy'); // 削除

        // 仕入先
        Route::get('supplier/list', 'SupplierController@index'); // 一覧
        Route::delete('supplier/{supplier}', 'SupplierController@destroy'); // 削除

        // 振込先口座
        Route::get('bank/find/tenponame', 'BankController@findTenpoName'); // 店舗情報を取得

        /////////////////////////////////////////////////

        /**
         * WEB管理
         */
        Route::namespace('Web')->prefix('web')->name('web.')->group(function () {
            // 予約
            Route::get('reserve/list', 'ReserveController@index'); // 一覧
            Route::get('reserve/{reserveNumber}', 'ReserveController@show'); // 詳細
            // Route::get('participant/search', 'ParticipantController@participantSearch'); // 顧客検索
            Route::delete('reserve/{hashId}', 'ReserveController@destroy'); // 削除
            Route::put('reserve/{reserveNumber}/no-cancel-charge/cancel', 'ReserveController@noCancelChargeCancel'); // キャンセル
            // Route::get('v_area/search', 'ReserveController@vAreaSearch'); // 国・地域検索
            Route::put('reserve/{reserveNumber}/status', 'ReserveController@statusUpdate'); // ステータスを更新

            // 見積
            Route::get('estimate/list', 'EstimateController@index'); // 一覧
            Route::get('estimate/{estimateNumber}', 'EstimateController@show'); // 詳細
            Route::delete('estimate/{hashId}', 'EstimateController@destroy'); // 削除
            Route::put('estimate/{requestNumber}/reject', 'EstimateController@reject'); // 相談依頼辞退
            Route::put('estimate/{requestNumber}/consent', 'EstimateController@consent'); // 相談承諾

            Route::put('estimate/{estimateNumber}/determine', 'EstimateController@determine')->name('estimate.determine'); // 見積確定
            Route::put('estimate/{estimateNumber}/status', 'EstimateController@statusUpdate'); // ステータスを更新
            
            Route::put("webreserveext/{webreserveextId}/online/change_request", "WebOnlineScheduleController@changeRequest"); // オンライン相談変更リクエスト
            Route::put("webreserveext/online/{id}/consent_request", "WebOnlineScheduleController@consentRequest"); // オンライン相談承諾

            // メッセージ送信
            Route::post('estimate/{reserveId}/message', 'WebMessageController@store'); // 作成
            Route::get('estimate/{reserveId}/message/list', 'WebMessageController@index'); // 作成
            Route::post('estimate/{reserveId}/message/read/check', 'WebMessageController@checkRead'); // 既読チェック
            Route::post('estimate/{reserveId}/message/read', 'WebMessageController@read'); // 既読処理
            
            Route::get('modelcourse/list', 'ModelcourseController@index'); // モデルコース一覧
            Route::put('modelcourse/{modelcourseId}/show', 'ModelcourseController@showUpdate'); // 表示フラグを更新
            Route::post('modelcourse/copy/{modelcourseId}', 'ModelcourseController@copy'); // 複製
            Route::delete('modelcourse/{modelcourseId}', 'ModelcourseController@destroy'); // 削除
        });

    });
});
