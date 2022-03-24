<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::pattern('staff', '[0-9]+');
Route::pattern('userCustomCategory', '[0-9]+');
Route::pattern('userCustomCategoryItem', '[0-9]+');
Route::pattern('applicationStep', 'normal|reserve');
Route::pattern('controlNumber', '[0-9A-Z\-]+');
Route::pattern('courseNo', 'MD[0-9]{4}');

Route::get('/', 'Staff\DefaultController@index');

// スタッフ（旅行会社）
Route::domain(env('STAFF_DOMAIN', 'asp.hakken-tour.com'))->namespace('Staff')->name('staff.')->prefix('{agencyAccount}')->group(function () {

    // ログイン認証関連
    Auth::routes([
        'register' => true,
        'reset'    => false,
        'verify'   => false
    ]);

    // ログイン認証後
    Route::middleware('auth:staff', 'check.company')->group(function () {
        // TOPページ
        // Route::resource('home', 'HomeController', ['only' => 'index']);
        // 個人顧客
        // Route::get('individuals', 'IndividualController@index')->name('individuals.index');

        // // カスタム項目
        // Route::resource('individual/custom_items', 'UserCustomItemController');
        // Route::get('individual/custom_items', 'UserCustomItemController@index')->name('custom_items.index');
        // Route::get('individual/custom_items/{category}/create', 'UserCustomItemController@create')->name('custom_items.create');
        // Route::post('individual/custom_items/{category}', 'UserCustomItemController@store')->name('custom_items.store');
        // Route::get('individual/custom_items/{userCustomItem}/list/create', 'UserCustomItemController@createList')->name('custom_items.list.create');
        // Route::post('individual/custom_items/{userCustomItem}/list', 'UserCustomItemController@storeList')->name('custom_items.list.store');

        // ファイルダウンロード管理
        Route::get('pdf/document/{category}/{hashId}/{output?}', 'FileController@documentPdf'); // 各種帳票pdfファイルのダウンロード

        // 予約/見積
        Route::prefix('estimates/asp')->name('asp.estimates.')->group(function () {
            // 予約管理
            Route::prefix('reserve')->name('reserve.')->group(function () {
                Route::get('index', 'ReserveController@index')->name('index'); // 一覧
                Route::get('create', 'ReserveController@create')->name('create'); // 作成画面
                Route::post('store', 'ReserveController@store')->name('store'); // 作成処理
                Route::get('/{reserveNumber}', 'ReserveController@show')->name('show'); // 表示
                Route::get('/{reserveNumber}/edit', 'ReserveController@edit')->name('edit'); // 編集ページ
                Route::put('/{reserveNumber}', 'ReserveController@update')->name('update'); // 更新処理

                // 請求書
                Route::get('/{reserveNumber}/invoice', 'ReserveInvoiceController@edit')->name('invoice.edit'); // 新規作成＆編集ページ

                // 領収書
                Route::get('/{reserveNumber}/receipt', 'ReserveReceiptController@edit')->name('receipt.edit'); // 新規作成＆編集ページ

                Route::get('{reserveNumber}/cancel_charge', 'ReserveController@cancelCharge')->name('cancel_charge.edit'); // キャンセルチャージページ(新規・編集共通)
                Route::post('{reserveNumber}/cancel_charge', 'ReserveController@cancelChargeUpdate')->name('cancel_charge.update'); // キャンセルチャージ処理

                // 参加者キャンセル(予約時のみ)
                Route::get('{reserveNumber}/participant/{id}/cancel_charge', 'ParticipantController@cancelCharge')->name('participant_cancel_charge.edit'); // キャンセルチャージページ(新規・編集共通)
                Route::post('{reserveNumber}/participant/{id}/cancel_charge', 'ParticipantController@cancelChargeUpdate')->name('participant_cancel_charge.update'); // キャンセルチャージ処理

            });

            // 見積管理
            Route::prefix('normal')->name('normal.')->group(function () {
                Route::get('index', 'EstimateController@index')->name('index'); // 一覧
                Route::get('create', 'EstimateController@create')->name('create'); // 作成画面
                Route::post('store', 'EstimateController@store')->name('store'); // 作成処理
                Route::get('/{estimateNumber}', 'EstimateController@show')->name('show'); // 表示
                Route::get('/{estimateNumber}/edit', 'EstimateController@edit')->name('edit'); // 編集ページ
                Route::put('/{estimateNumber}', 'EstimateController@update')->name('update'); // 更新処理

            });

            // 旅程管理
            Route::get('/{applicationStep}/{controlNumber}/itinerary/create', 'ReserveItineraryController@create')->name('itinerary.create'); // 作成画面
            // Route::post('/{applicationStep}/{controlNumber}/itinerary', 'ReserveItineraryController@store')->name('itinerary.store'); // 作成処理
            Route::get('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/edit', 'ReserveItineraryController@edit')->name('itinerary.edit'); // 編集画面
            // Route::put('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}', 'ReserveItineraryController@update')->name('itinerary.update'); // 更新処理
            
            Route::get('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/schedule_pdf', 'ReserveItineraryController@schedulePdf')->name('itinerary.pdf'); // 行程PDF
            Route::get('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/rooming_list/pdf', 'ReserveItineraryController@itineraryRoomingListPdf')->name('itinerary_roominglist.pdf'); // ルーミングリストpdf（当該行程のルーミングリスト）

            Route::get('/{applicationStep}/{controlNumber}/itinerary/rooming_list/pdf', 'ReserveItineraryController@roomingListPdf')->name('roominglist.pdf'); // ルーミングリストpdf（当該宿泊施設の当該日リスト）

            // 見積・予約確認書
            Route::get('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/confirm', 'ReserveConfirmController@create')->name('reserve_confirm.create'); // 新規作成ページ
            Route::get('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/confirm/{confirmNumber}/edit', 'ReserveConfirmController@edit')->name('reserve_confirm.edit'); // 編集ページ

        });

        // 催行済み
        Route::prefix('estimates/departed')->name('estimates.departed.')->group(function () {
            Route::get('index', 'DepartedController@index')->name('index'); // 一覧
            Route::get('/{reserveNumber}', 'DepartedController@show')->name('show'); // 表示
        });


        // 顧客管理
        Route::prefix('client')->name('client.')->group(function () {
            // 個人顧客
            Route::prefix('person')->name('person.')->group(function () {
                Route::get('index', 'UserController@index')->name('index'); // 一覧
                Route::get('create', 'UserController@create')->name('create'); // 作成
                Route::post('store', 'UserController@store')->name('store'); // 作成処理
                Route::get('/{userNumber}', 'UserController@show')->name('show'); // 表示
                Route::get('/{userNumber}/edit', 'UserController@edit')->name('edit'); // 編集ページ
                Route::put('/{userNumber}', 'UserController@update')->name('update'); // 更新処理
            });
            // 法人顧客
            Route::prefix('business')->name('business.')->group(function () {
                Route::get('index', 'BusinessUserController@index')->name('index'); // 一覧
                Route::get('create', 'BusinessUserController@create')->name('create'); // 作成
                Route::post('store', 'BusinessUserController@store')->name('store'); // 作成処理
                Route::get('/{userNumber}', 'BusinessUserController@show')->name('show'); // 表示
                Route::get('/{userNumber}/edit', 'BusinessUserController@edit')->name('edit'); // 編集ページ
                Route::put('/{userNumber}', 'BusinessUserController@update')->name('update'); // 更新処理

            });
        });

        // 相談履歴
        Route::prefix('consultation')->name('consultation.')->group(function () {
            Route::get('index', 'ConsultationController@index')->name('index'); // 相談一覧
            Route::get('message/index', 'ConsultationController@messageIndex')->name('message.index'); // メッセージ履歴一覧(Web用だけどここに間借り)
        });

        // 業務管理
        Route::prefix('management')->name('management.')->group(function () {
            // 請求管理
            Route::prefix('invoice')->name('invoice.')->group(function () {
                Route::get('index', 'ManagementInvoiceController@index')->name('index'); // 一覧
            });
            // 一括請求管理
            Route::prefix('bundle_invoice')->name('bundle_invoice.')->group(function () {
                Route::get('{reserveBundleInvoiceHashId}/breakdown', 'ManagementInvoiceController@breakdown')->name('breakdown'); // 一括請求内訳一覧

                Route::get('/{reserveBundleInvoiceHashId}', 'ReserveBundleInvoiceController@edit')->name('edit'); // 新規作成＆編集ページ

            });

            // 領収書(一括請求用)
            Route::prefix('bundle_receipt')->name('bundle_receipt.')->group(function () {
                Route::get('/{reserveBundleInvoiceHashId}', 'ReserveBundleReceiptController@edit')->name('edit'); // 新規作成＆編集ページ
            });

            // 支払管理
            Route::prefix('payment')->name('payment.')->group(function () {
                Route::get('index', 'ManagementPaymentController@index')->name('index'); // 一覧
            });
        });


        // マスタ管理
        Route::prefix('master')->name('master.')->group(function () {
            // 方面
            Route::prefix('direction')->name('direction.')->group(function () {
                Route::get('index', 'DirectionController@index')->name('index'); // 一覧
                Route::get('create', 'DirectionController@create')->name('create'); // 作成
                Route::post('store', 'DirectionController@store')->name('store'); // 作成処理
                Route::get('/{uuid}/edit', 'DirectionController@edit')->name('edit'); // 更新
                Route::put('{uuid}', 'DirectionController@update')->name('update'); // 更新処理
                Route::delete('{uuid}', 'DirectionController@destroy')->name('destroy'); // 削除
            });

            // 国・地域
            Route::prefix('area')->name('area.')->group(function () {
                Route::get('index', 'AreaController@index')->name('index'); // 一覧
                Route::get('create', 'AreaController@create')->name('create'); // 作成
                Route::post('store', 'AreaController@store')->name('store'); // 作成処理
                Route::get('/{uuid}/edit', 'AreaController@edit')->name('edit'); // 更新
                Route::put('{uuid}', 'AreaController@update')->name('update'); // 更新処理
                Route::delete('{uuid}', 'AreaController@destroy')->name('destroy'); // 削除
            });

            // 都市・空港
            Route::prefix('city')->name('city.')->group(function () {
                Route::get('index', 'CityController@index')->name('index'); // 一覧
                Route::get('create', 'CityController@create')->name('create'); // 作成
                Route::post('store', 'CityController@store')->name('store'); // 作成処理
                Route::get('/{city}/edit', 'CityController@edit')->name('edit'); // 更新
                Route::put('{city}', 'CityController@update')->name('update'); // 更新処理
                Route::delete('{city}', 'CityController@destroy')->name('destroy'); // 削除
            });

            // 仕入れ先
            Route::prefix('supplier')->name('supplier.')->group(function () {
                Route::get('index', 'SupplierController@index')->name('index'); // 一覧
                Route::get('create', 'SupplierController@create')->name('create'); // 作成
                Route::post('store', 'SupplierController@store')->name('store'); // 作成処理
                Route::get('/{supplier}/edit', 'SupplierController@edit')->name('edit'); // 更新
                Route::put('{supplier}', 'SupplierController@update')->name('update'); // 更新処理
                Route::delete('{supplier}', 'SupplierController@destroy')->name('destroy'); // 削除
            });

            /* 科目 */
            Route::prefix('subject')->name('subject.')->group(function () {
                Route::get('index', 'SubjectController@index')->name('index'); // top
                Route::get('create', 'SubjectController@create')->name('create'); // 作成

                Route::prefix(config('consts.subject_categories.SUBJECT_CATEGORY_OPTION'))->name(config('consts.subject_categories.SUBJECT_CATEGORY_OPTION') . '.')->group(function () { // オプション科目
                    Route::post('store', 'SubjectOptionController@store')->name('store'); // 作成処理
                    Route::get('/{subjectOption}/edit', 'SubjectOptionController@edit')->name('edit'); // 更新
                    Route::put('{subjectOption}', 'SubjectOptionController@update')->name('update'); // 更新処理
                    Route::delete('{subjectOption}', 'SubjectOptionController@destroy')->name('destroy'); // 削除
                });

                Route::prefix(config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE'))->name(config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE') . '.')->group(function () { // 航空券科目
                    Route::post('store', 'SubjectAirplaneController@store')->name('store'); // 作成処理
                    Route::get('/{subjectAirplane}/edit', 'SubjectAirplaneController@edit')->name('edit'); // 更新
                    Route::put('{subjectAirplane}', 'SubjectAirplaneController@update')->name('update'); // 更新処理
                    Route::delete('{subjectAirplane}', 'SubjectAirplaneController@destroy')->name('destroy'); // 削除
                });

                Route::prefix(config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL'))->name(config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL') . '.')->group(function () { // ホテル科目
                    Route::post('store', 'SubjectHotelController@store')->name('store'); // 作成処理
                    Route::get('/{subjectHotel}/edit', 'SubjectHotelController@edit')->name('edit'); // 更新
                    Route::put('{subjectHotel}', 'SubjectHotelController@update')->name('update'); // 更新処理
                    Route::delete('{subjectHotel}', 'SubjectHotelController@destroy')->name('destroy'); // 削除
                });
            });
        });

        // システム設定
        Route::prefix('system')->name('system.')->group(function () {
            // Route::resource('user', 'StaffController', ['except' => ['show']]); // ユーザー管理

            Route::get('user/index', 'StaffController@index')->name('user.index');
            Route::get('user/create', 'StaffController@create')->name('user.create');
            Route::post('user/store', 'StaffController@store')->name('user.store');
            Route::get('user/{account}/edit', 'StaffController@edit')->name('user.edit');
            Route::put('user/{account}', 'StaffController@update')->name('user.update');
            Route::delete('user/{account}', 'StaffController@destroy')->name('user.destroy');

            Route::resource('role', 'AgencyRoleController', ['except' => ['show']]); // ユーザー権限。authorityにするとgetのIDパラメータ（authority）とpostのフィールド名が被ってしまうのでバリデーション時都合が悪くroleに

            Route::resource('mail', 'MailTemplateController', ['except' => ['show']]); // メールテンプレート

            /*　帳票設定 */
            Route::prefix('document')->name('document.')->group(function () {
                Route::get('index', 'DocumentCategoryController@index')->name('index'); // top
                Route::prefix(config('consts.document_categories.DOCUMENT_CATEGORY_COMMON'))->name(config('consts.document_categories.DOCUMENT_CATEGORY_COMMON') . '.')->group(function () { // 共通設定
                    Route::get('create', 'DocumentCommonController@create')->name('create'); // 作成
                    Route::post('store', 'DocumentCommonController@store')->name('store'); // 作成処理
                    Route::get('/{documentCommon}/edit', 'DocumentCommonController@edit')->name('edit'); // 更新
                    Route::put('{documentCommon}', 'DocumentCommonController@update')->name('update'); // 更新処理
                    Route::delete('{documentCommon}', 'DocumentCommonController@destroy')->name('destroy'); // 削除
                });

                Route::prefix(config('consts.document_categories.DOCUMENT_CATEGORY_QUOTE'))->name(config('consts.document_categories.DOCUMENT_CATEGORY_QUOTE') . '.')->group(function () { // 見積/予約確認書設定
                    Route::get('create', 'DocumentQuoteController@create')->name('create'); // 作成
                    Route::post('store', 'DocumentQuoteController@store')->name('store'); // 作成処理
                    Route::get('/{documentQuote}/edit', 'DocumentQuoteController@edit')->name('edit'); // 更新
                    Route::put('{documentQuote}', 'DocumentQuoteController@update')->name('update'); // 更新処理
                    Route::delete('{documentQuote}', 'DocumentQuoteController@destroy')->name('destroy'); // 削除
                });

                Route::prefix(config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST'))->name(config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST') . '.')->group(function () { // 請求書設定設定
                    Route::get('create', 'DocumentRequestController@create')->name('create'); // 作成
                    Route::post('store', 'DocumentRequestController@store')->name('store'); // 作成処理
                    Route::get('/{documentRequest}/edit', 'DocumentRequestController@edit')->name('edit'); // 更新
                    Route::put('{documentRequest}', 'DocumentRequestController@update')->name('update'); // 更新処理
                    Route::delete('{documentRequest}', 'DocumentRequestController@destroy')->name('destroy'); // 削除
                });

                Route::prefix(config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL'))->name(config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL') . '.')->group(function () { // 一括請求書設定
                    Route::get('create', 'DocumentRequestAllController@create')->name('create'); // 作成
                    Route::post('store', 'DocumentRequestAllController@store')->name('store'); // 作成処理
                    Route::get('/{documentRequestAll}/edit', 'DocumentRequestAllController@edit')->name('edit'); // 更新
                    Route::put('{documentRequestAll}', 'DocumentRequestAllController@update')->name('update'); // 更新処理
                    Route::delete('{documentRequestAll}', 'DocumentRequestAllController@destroy')->name('destroy'); // 削除
                });

                Route::prefix(config('consts.document_categories.DOCUMENT_CATEGORY_RECEIPT'))->name(config('consts.document_categories.DOCUMENT_CATEGORY_RECEIPT') . '.')->group(function () { // 領収書
                    Route::get('create', 'DocumentReceiptController@create')->name('create'); // 作成
                    Route::post('store', 'DocumentReceiptController@store')->name('store'); // 作成処理
                    Route::get('/{documentReceipt}/edit', 'DocumentReceiptController@edit')->name('edit'); // 更新
                    Route::put('{documentReceipt}', 'DocumentReceiptController@update')->name('update'); // 更新処理
                    // Route::delete('{documentReceipt}', 'DocumentReceiptController@destroy')->name('destroy'); // 削除
                });
            });
            
            Route::name('custom.')->group(function () { // カスタム項目
                
                // top
                Route::get('custom', 'UserCustomItemController@index')->name('index');
                Route::delete('custom/{userCustomItem}', 'UserCustomItemController@destroy')->name('delete'); // 項目削除

                // テキスト項目
                Route::name('text.')->group(function () {
                    Route::get('custom/text/create', 'UserCustomItemController@createText')->name('create'); // 作成フォーム
                    Route::post('custom/text', 'UserCustomItemController@storeText')->name('store'); // 作成処理
                    Route::get('custom/text/{userCustomItem}/edit', 'UserCustomItemController@editText')->name('edit'); // 編集
                    Route::put('custom/text/{userCustomItem}', 'UserCustomItemController@updateText')->name('update'); // 編集処理
                });

                // 日時項目
                Route::name('date.')->group(function () {
                    Route::get('custom/date/create', 'UserCustomItemController@createDate')->name('create'); // 作成フォーム
                    Route::post('custom/date', 'UserCustomItemController@storeDate')->name('store'); // 作成処理
                    Route::get('custom/date/{userCustomItem}/edit', 'UserCustomItemController@editDate')->name('edit'); // 編集
                    Route::put('custom/date/{userCustomItem}', 'UserCustomItemController@updateDate')->name('update'); // 編集処理
                });

                // リスト項目
                Route::name('list.')->group(function () {
                    Route::get('custom/list/create', 'UserCustomItemController@createList')->name('create'); // 作成フォーム
                    Route::post('custom/list', 'UserCustomItemController@storeList')->name('store'); // 作成処理
                    Route::get('custom/list/{userCustomItem}/edit', 'UserCustomItemController@editList')->name('edit'); // 編集
                    Route::put('custom/list/{userCustomItem}', 'UserCustomItemController@updateList')->name('update'); // 編集処理
                });
            });
        });


        /**
         * WEBページ管理
         */
        
        // Web予約・見積管理
        Route::prefix('estimates/web')->name('web.estimates.')->namespace('Web')->group(function () {
            // 予約管理
            Route::prefix('reserve')->name('reserve.')->group(function () {
                Route::get('index', 'ReserveController@index')->name('index'); // 一覧
                Route::get('/{reserveNumber}', 'ReserveController@show')->name('show'); // 表示
                Route::get('/{reserveNumber}/edit', 'ReserveController@edit')->name('edit'); // 編集ページ
                Route::put('/{reserveNumber}', 'ReserveController@update')->name('update'); // 更新処理

                // 請求書
                Route::get('/{reserveNumber}/invoice', 'ReserveInvoiceController@edit')->name('invoice.edit'); // 新規作成＆編集ページ

                // 領収書
                Route::get('/{reserveNumber}/receipt', 'ReserveReceiptController@edit')->name('receipt.edit'); // 新規作成＆編集ページ

                // 予約キャンセル
                Route::get('{reserveNumber}/cancel_charge', 'ReserveController@cancelCharge')->name('cancel_charge.edit'); // キャンセルチャージページ(新規・編集共通)
                Route::post('{reserveNumber}/cancel_charge', 'ReserveController@cancelChargeUpdate')->name('cancel_charge.update'); // キャンセルチャージ処理


                // 参加者キャンセル(予約時のみ)
                Route::get('{reserveNumber}/participant/{id}/cancel_charge', 'ParticipantController@cancelCharge')->name('participant_cancel_charge.edit'); // キャンセルチャージページ(新規・編集共通)
                Route::post('{reserveNumber}/participant/{id}/cancel_charge', 'ParticipantController@cancelChargeUpdate')->name('participant_cancel_charge.update'); // キャンセルチャージ処理

            });

            // 見積管理
            Route::prefix('normal')->name('normal.')->group(function () {
                Route::get('index', 'EstimateController@index')->name('index'); // 一覧
                Route::get('/request/{requestNumber}', 'EstimateController@request')->name('request'); // 相談リクエスト詳細
                Route::get('/{estimateNumber}', 'EstimateController@show')->name('show'); // 見積詳細
                Route::get('/{estimateNumber}/edit', 'EstimateController@edit')->name('edit'); // 編集ページ
                Route::put('/{estimateNumber}', 'EstimateController@update')->name('update'); // 更新処理
            });

            // 旅程管理
            Route::get('/{applicationStep}/{controlNumber}/itinerary/create', 'ReserveItineraryController@create')->name('itinerary.create'); // 作成画面
            // Route::post('/{applicationStep}/{controlNumber}/itinerary', 'ReserveItineraryController@store')->name('itinerary.store'); // 作成処理
            Route::get('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/edit', 'ReserveItineraryController@edit')->name('itinerary.edit'); // 編集画面
            // Route::put('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}', 'ReserveItineraryController@update')->name('itinerary.update'); // 更新処理

            Route::get('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/schedule_pdf', 'ReserveItineraryController@schedulePdf')->name('itinerary.pdf'); // 行程PDF
            Route::get('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/rooming_list/pdf', 'ReserveItineraryController@itineraryRoomingListPdf')->name('itinerary_roominglist.pdf'); // ルーミングリストpdf（当該行程のルーミングリスト）

            Route::get('/{applicationStep}/{controlNumber}/itinerary/rooming_list/pdf', 'ReserveItineraryController@roomingListPdf')->name('roominglist.pdf'); // ルーミングリストpdf（当該宿泊施設の当該日リスト）

            // 見積・予約確認書
            Route::get('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/confirm', 'ReserveConfirmController@create')->name('reserve_confirm.create'); // 新規作成ページ
            Route::get('/{applicationStep}/{controlNumber}/itinerary/{itineraryNumber}/confirm/{confirmNumber}/edit', 'ReserveConfirmController@edit')->name('reserve_confirm.edit'); // 編集ページ

        });

        Route::prefix('front')->name('front.')->namespace('Web')->group(function () {

            /** 会社情報 */
            Route::get('company', 'CompanyController@edit')->name('company.edit'); // 会社情報編集ページ
            Route::post('company', 'CompanyController@upsert')->name('company.update'); // 会社情報更新処理

            /** プロフィール管理 */
            Route::get('profile', 'ProfileController@edit')->name('profile.edit');
            // プロフィール編集
            Route::post('profile', 'ProfileController@upsert')->name('profile.update'); //プロフィール更新処理

            // モデルコース管理
            Route::prefix('modelcourse')->name('modelcourse.')->group(function () {
                Route::get('index', 'ModelcourseController@index')->name('index'); // 一覧
                Route::get('/create', 'ModelcourseController@create')->name('create'); // 新規
                Route::post('/', 'ModelcourseController@store')->name('store'); // 作成処理
                Route::get('/{courseNo}', 'ModelcourseController@show')->name('show'); // 詳細
                Route::get('/{courseNo}/edit', 'ModelcourseController@edit')->name('edit'); // 編集
                Route::put('/{courseNo}', 'ModelcourseController@update')->name('update'); // 更新処理
            });
        });

        // ↓以下は、動作テストのための暫定プログラム
        Route::get('chat/{consultation}', 'ChatController@index')->name('chat.index');
    });
});
