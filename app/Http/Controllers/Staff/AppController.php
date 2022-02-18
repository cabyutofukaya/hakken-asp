<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppController extends Controller
{
    // 不認可リダイレクト
    public function forbiddenRedirect($errorMessage=null)
    {
        $referrer = request()->headers->get('referer');
        if ($referrer) {
            return redirect()->to($referrer)->withErrors(array('auth_error' => $errorMessage));
        }
        return redirect()->to('/');
    }
}
