<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnlineController extends Controller
{
    /**
     * @param string $consultationId 相談ID
     */
    public function index($consultationId)
    {
        return view('user.online.index');
    }
}
