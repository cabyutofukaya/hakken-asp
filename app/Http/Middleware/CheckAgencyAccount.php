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
        if ($staff->agency->status != config('consts.agencies.STATUS_MAIN_REGISTRATION')) { // 会社のステータスをチェック
            Auth::guard('staff')->logout();
            abort(403, "ERROR。会社アカウントが停止されています");
        };
        if ($staff->status != config('consts.staffs.STATUS_VALID')) { // スタッフのステータスをチェック
            Auth::guard('staff')->logout();
            abort(403);
        };
        // 販売プラン検討中につきトライアルのチェックはひとまず不要
        // if (!$staff->agency->is_trial() && !$staff->agency->is_definitive()) {
        //     Auth::guard('staff')->logout();
        //     throw new PromotionException;
        // }
        return $next($request);
    }
}
