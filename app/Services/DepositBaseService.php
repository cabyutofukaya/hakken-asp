<?php

namespace App\Services;

/**
 * AgencyDepositServiceとAgencyBundleDepositServiceの基底class
 */
class DepositBaseService
{
    /**
     * 入金識別IDを生成
     * 
     * @return string
     */
    public function generateIdentifierId()
    {
        return uniqid(rand());
    }
}
