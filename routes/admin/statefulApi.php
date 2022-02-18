<?php

use Illuminate\Http\Request;

Route::pattern('id', '[0-9]+');


Route::domain(env('ADMIN_DOMAIN', 'cab.hakken-tour.com'))->namespace('Admin\Api')->name('admin.api.')->prefix('api')->group(function () {
  // 要認証API
  Route::middleware('api_auth:admin')->group(function () {
    Route::post('/agency/is-account-exists', 'AgencyController@isAccountExistsApi'); // アカウントの重複チェック
    Route::get('/agency/select-search-companyname', 'AgencyController@selectSearchCompanyNameApi'); // 名前検索
    Route::put('agency/{id}/status', 'AgencyController@updateStatus'); // 会社削除
    Route::delete('agency/{id}', 'AgencyController@destroy'); // 会社削除

    Route::put('web_user/{id}/status', 'WebUserController@updateStatus'); // ステータスを更新
    Route::delete('web_user/{id}', 'WebUserController@destroy'); // ユーザー削除

  });
});
