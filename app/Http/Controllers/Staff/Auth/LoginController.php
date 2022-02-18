<?php

namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AgencyService;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    // protected $redirectTo = RouteServiceProvider::STAFF_HOME;

    private $agencyService;

    public function __construct(AgencyService $agencyService)
    {
        $this->middleware('guest:staff')->except('logout');

        $this->agencyService = $agencyService;
    }

    protected function redirectTo()
    {
        // return sprintf("/%s/home", Auth::guard('staff')->user()->agency->account);
        return route('staff.asp.estimates.reserve.index', Auth::guard('staff')->user()->agency->account);
    }

    protected function guard()
    {
        return Auth::guard('staff');
    }

    public function showLoginForm(Request $request)
    {
        // 会社Account → 会社IDを取得
        $agencyId = $this->agencyService->getIdByAccount($request->agencyAccount);

        return view('staff.auth.login', compact('agencyId'));
    }

    public function logout(Request $request)
    {
        Auth::guard('staff')->logout();
        session()->flush(); // ←これを入れないと、ログアウト直後にログインしてもログインできなくなる

        return $this->loggedOut($request);
    }

    public function loggedOut(Request $request)
    {
        return redirect(route('staff.login', $request->agencyAccount));
    }

    protected function credentials(Request $request)
    {
        $request->merge(['status' => config("consts.staffs.STATUS_VALID")]);
        return $request->only($this->username(), 'password', 'agency_id', 'status'); // 「account + password + agency_id + status = 1」の組み合わせで認証処理。所属会社のstatusが適切かどうかは、App\Providers\StaffAuthServiceProvider@retrieveByCredentials でチェック
    }

    // protected function authenticated(Request $request, $user)
    // {
    //     // 同時ログイン禁止処理
    //     auth('staff')->logoutOtherDevices($request->input('password'));
    // }

    /**
     * ログインIDにaccountを使用
     */
    public function username()
    {
        return 'account';
    }

    /**
     * 最終ログイン日時を記録
     */
    protected function authenticated(Request $request, $staff)
    {
        $staff->last_login_at = now();
        $staff->save();
    }
}
