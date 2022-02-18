<?php

namespace App\Http\Controllers\User\Api;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * バージョン文字列が等しいか
     * 
     * @param string $versionString
     * @param int $version
     * @return boolean
     */
    protected function sameVersion(string $versionString, int $version)
    {
        return $version === (int)substr($versionString, 1);
    }
}
