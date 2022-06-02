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

// // 管理者
// Route::domain(env('ADMIN_DOMAIN', 'cab.hakken-tour.com'))->namespace('Admin')->name('admin.')->group(function () {

//     // ログイン認証関連
//     Auth::routes([
//         'register' => true,
//         'reset'    => false,
//         'verify'   => false
//     ]);
    
//     Route::get('/', 'DefaultController@index');
//     // Route::resource('home', 'HomeController', ['only' => 'index']);

//     // ログイン認証後
//     Route::middleware('auth:admin')->group(function () {

//         // TOPページ
//         // Route::resource('home', 'HomeController', ['only' => 'index']);

//         Route::resource('prefectures', 'PrefectureController');
//         Route::resource('inflows', 'InflowController', ['only' => ['index', 'create', 'edit', 'store', 'update', 'destroy']]);

//         Route::resource('users', 'UserController');
//         Route::resource('agencies', 'AgencyController');
//         Route::resource('roles', 'RoleController');

//         Route::get('agencies/{agency}/staffs/{staff}', 'StaffController@show')->name('staffs.show');
//         Route::get('agencies/{agency}/staffs/{staff}/edit', 'StaffController@edit')->name('staffs.edit');
//         Route::put('agencies/{agency}/staffs/{staff}', 'StaffController@update')->name('staffs.update');
//         Route::get('agencies/{agency}/staffs/create', 'StaffController@create')->name('staffs.create');
//         Route::post('agencies/{agency}/staffs', 'StaffController@store')->name('staffs.store');
//         Route::delete('agencies/{agency}/staffs/{staff}', 'StaffController@destroy')->name('staffs.destroy');

//         Route::get('model_logs', 'ModelLogController@index')->name('model_logs.index');

//         Route::get('act_logs', 'ActLogController@index')->name('act_logs.index');

//         // HAKKEN機能
//         Route::namespace('Hakken')->name('hakken.')->group(function () {
//             Route::resource('purposes', 'PurposeController');
//             Route::resource('interests', 'InterestController');    
//         });

//     });

// });
