<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\PromotionException;

class CheckAgencyAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        /**
         * URLの{agencyAccount}部の有効性をチェック
         *
         * １、ログインしているスタッフの会社アカウントとの紐付けが正しいか
         * ２、当該会社のステータスが有効か
         */
        if (!($staff = Auth::guard('staff')->user())) {
            Auth::guard('staff')->logout();
            abort(403);
        }
        if ($request->agencyAccount !== $staff->agency->account) {
            Auth::guard('staff')->logout();
            abort(403);
        }
        if ($staff->agency->status != config('consts.agencies.STATUS_MAIN_REGISTRATION')) { // ステータスチェック
            Auth::guard('staff')->logout();
            abort(403);
        };
        if (!$staff->agency->is_trial() && !$staff->agency->is_definitive()) {
            Auth::guard('staff')->logout();
            throw new PromotionException;
        }
        return $next($request);
    }
}
