<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * @param string $consultationId 相談ID
     */
    public function index($consultationId)
    {
        return view('staff.chat.index');
    }
}
