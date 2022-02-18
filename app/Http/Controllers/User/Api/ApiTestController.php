<?php

namespace App\Http\Controllers\User\Api;

use Validator;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiTestController extends BaseController
{
    // public function __construct()
    // {
    //     $this->guard = "api";
    // }

    public function test(){
        echo "hello api test.";
    }

}
