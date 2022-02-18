<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    // logoutOtherDevicesで強制ログアウトさせた場合、本メソッドでログインページへユーザーを確実にリダイレクトさせる必要がある（agencyのログインがうまく動かなくなるので、logoutOtherDevicesは一旦未使用）
    // ↓マルチログインだと、loginページ転送処理がうまく動作しないので各guardに応じたloginページを指定できるように実装
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $exception->getMessage()], 401);
        } else {
            if ($request->getHttpHost() === env('STAFF_DOMAIN')) {
                return redirect()->guest(route('staff.login', $request->agencyAccount));
            } elseif ($request->getHttpHost() === env('ADMIN_DOMAIN')) {
                return redirect()->guest(route('admin.login'));
            } else {
                return redirect()->guest(route('login'));
            }
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof TokenMismatchException) { // トークン有効期限切れ
            return back()
                ->withInput($request->except('_token'))
                ->withErrors(['error' => ['トークンが無効です']]);
        }
        if ($exception instanceof PromotionException) { // トライアルも正式版契約もされていない場合
            return back()
                ->withInput($request->except('_token'))
                ->withErrors(['error' => ['有効な販売設定が見つかりませんでした。契約内容をご確認ください']]);
        }
        return parent::render($request, $exception);
    }
}
