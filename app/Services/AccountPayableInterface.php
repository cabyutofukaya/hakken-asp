<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

interface AccountPayableInterface
{
    public function updateStatusAndUnpaidBalance($id, int $unpaidBalance, $status) : Model;
}
