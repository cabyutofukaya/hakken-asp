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
// Route::pattern('agency', '[0-9]+');

// 管理者
Route::domain(env('ADMIN_DOMAIN', 'cab.hakken-tour.com'))->namespace('Admin')->name('admin.')->group(function () {

    // ログイン認証関連
    Auth::routes([
        'register' => true,
        'reset'    => false,
        'verify'   => false
    ]);
    
    Route::get('/', 'DefaultController@index');

    // ログイン認証後
    Route::middleware('auth:admin')->group(function () {

        // TOPページ
        Route::resource('home', 'HomeController', ['only' => 'index']);

        Route::resource('prefectures', 'PrefectureController');
        // Route::resource('inflows', 'InflowController', ['only' => ['index', 'create', 'edit', 'store', 'update', 'destroy']]);
        
        Route::resource('users', 'UserController');
        Route::resource('agencies', 'AgencyController', ['except' => ['destroy']]);
        Route::resource('roles', 'RoleController');

        // 銀行データ
        Route::prefix('banks')->name('banks.')->group(function () {
            Route::get('import/csv', 'BankController@createCsvImport')->name("import.create");
            Route::post('import/csv', 'BankController@storeCsvImport')->name("import.store");
        });

        // 方面データ
        Route::prefix('areas')->name('areas.')->group(function () {
            Route::get('master_directions/import/csv', 'MasterDirectionController@createCsvImport')->name("master_directions.import.create");
            Route::post('master_directions/import/csv', 'MasterDirectionController@storeCsvImport')->name("master_directions.import.store");
            // 国地域データ
            Route::get('master_areas/import/csv', 'MasterAreaController@createCsvImport')->name("master_areas.import.create");
            Route::post('master_areas/import/csv', 'MasterAreaController@storeCsvImport')->name("master_areas.import.store");
        });

        // 会社管理
        Route::prefix('agencies')->name('agencies.')->group(function () {
            Route::get('{agency}/staffs/{staff}', 'StaffController@show')->name('staffs.show');
            Route::get('{agency}/staffs/{staff}/edit', 'StaffController@edit')->name('staffs.edit');
            Route::put('{agency}/staffs/{staff}', 'StaffController@update')->name('staffs.update');
            Route::get('{agency}/staffs/create', 'StaffController@create')->name('staffs.create');
            Route::post('{agency}/staffs', 'StaffController@store')->name('staffs.store');
            Route::delete('{agency}/staffs/{staff}', 'StaffController@destroy')->name('staffs.destroy');
        });

        // HAKKEN機能
        Route::namespace('Web')->name('web.')->group(function () {
            Route::resource('web_users', 'BaseWebUserController');
            Route::resource('purposes', 'PurposeController');
            Route::resource('', 'InterestController');

            // 通知機能
            Route::resource('system_news', 'SystemNewsController', ['except' => ['show','destroy']]);
            Route::delete('system_news', 'SystemNewsController@delete')->name('system_news.delete');
        });

        Route::get('model_logs', 'ModelLogController@index')->name('model_logs.index');
        Route::get('act_logs', 'ActLogController@index')->name('act_logs.index');
    });
});
