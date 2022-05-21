<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

interface AccountPayableInterface
{
    public function updateStatusAndPaidBalance($id, int $amountPayment, int $unpaidBalance, $status) : Model;
}
